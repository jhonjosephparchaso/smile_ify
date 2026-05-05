<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    $_SESSION['updateError'] = "Unauthorized access.";
    header("Location: " . BASE_URL . "/Patient/pages/profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['dental_transaction_id'])) {
    $_SESSION['updateError'] = "Missing transaction ID.";
    header("Location: " . BASE_URL . "/Patient/pages/profile.php");
    exit();
}

$transaction_id = intval($_POST['dental_transaction_id']);

$stmtPatient = $conn->prepare("
    SELECT u.user_id, u.first_name, u.middle_name, u.last_name
    FROM users u
    INNER JOIN appointment_transaction atx ON atx.user_id = u.user_id
    INNER JOIN dental_transaction dt ON dt.appointment_transaction_id = atx.appointment_transaction_id
    WHERE dt.dental_transaction_id = ?
");
$stmtPatient->bind_param("i", $transaction_id);
$stmtPatient->execute();
$stmtPatient->bind_result($actual_patient_id, $first_name, $middle_name, $last_name);
if (!$stmtPatient->fetch()) {
    $stmtPatient->close();
    $_SESSION['updateError'] = "Patient not found for this transaction.";
    header("Location: " . BASE_URL . "/Patient/pages/profile.php");
    exit();
}
$stmtPatient->close();

$last_name_clean = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($last_name));
$middle_initial = $middle_name ? strtoupper(substr($middle_name, 0, 1)) . '. ' : '';
$patient_fullname = $first_name . ' ' . $middle_initial . $last_name;

$medcertPrice = 150;
$pstmt = $conn->prepare("SELECT price FROM service WHERE name='Dental Certificate' LIMIT 1");
$pstmt->execute();
$pstmt->bind_result($p);
$pstmt->fetch();
$pstmt->close();
if ($p !== null) $medcertPrice = $p;

$imagePath = null;

if ($medcertPrice > 0) {

    if (!isset($_FILES['payment_receipt']) || $_FILES['payment_receipt']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['updateError'] = "Payment screenshot is required.";
        header("Location: " . BASE_URL . "/Patient/pages/profile.php");
        exit();
    }

    $fileTmpPath = $_FILES['payment_receipt']['tmp_name'];
    $fileSize = $_FILES['payment_receipt']['size'];
    $fileType = mime_content_type($fileTmpPath);
    $fileExt = strtolower(pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION));

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024;

    if (!in_array($fileType, $allowedTypes)) {
        $_SESSION['updateError'] = "Invalid image type. Allowed: JPG, PNG, WEBP.";
        header("Location: " . BASE_URL . "/Patient/pages/profile.php");
        exit();
    }

    if ($fileSize > $maxFileSize) {
        $_SESSION['updateError'] = "Image exceeds 5MB limit.";
        header("Location: " . BASE_URL . "/Patient/pages/profile.php");
        exit();
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/images/payments/medcert_payments/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = $transaction_id . "_" . $last_name_clean . "." . $fileExt;
    $targetPath = $uploadDir . $fileName;

    foreach (glob($uploadDir . $transaction_id . "_*.*") as $oldFile) {
        if (is_file($oldFile)) unlink($oldFile);
    }

    if (!move_uploaded_file($fileTmpPath, $targetPath)) {
        $_SESSION['updateError'] = "File upload failed. Please try again.";
        header("Location: " . BASE_URL . "/Patient/pages/profile.php");
        exit();
    }

    $imagePath = "/images/payments/medcert_payments/" . $fileName;
}

$checkSql = "
    SELECT dt.dental_transaction_id, atx.appointment_transaction_id, atx.branch_id
    FROM dental_transaction dt
    INNER JOIN appointment_transaction atx
        ON dt.appointment_transaction_id = atx.appointment_transaction_id
    WHERE dt.dental_transaction_id = ?
        AND (atx.user_id = ? OR atx.user_id IN (SELECT user_id FROM users WHERE guardian_id = ?))
";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("iii", $transaction_id, $user_id, $user_id);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    $_SESSION['updateError'] = "Transaction not found or not owned by this user.";
    header("Location: " . BASE_URL . "/Patient/pages/profile.php");
    exit();
}

$checkStmt->bind_result($dt_id, $appointment_transaction_id, $branch_id);
$checkStmt->fetch();
$checkStmt->close();

$expiryCheckSql = "SELECT date_created FROM dental_transaction WHERE dental_transaction_id = ?";
$expiryStmt = $conn->prepare($expiryCheckSql);
$expiryStmt->bind_param("i", $transaction_id);
$expiryStmt->execute();
$expiryStmt->bind_result($date_created);
$expiryStmt->fetch();
$expiryStmt->close();

$updateSql = "
    UPDATE dental_transaction
    SET medcert_status = 'Requested',
        medcert_receipt = ?,
        medcert_requested_date = NOW(),
        date_updated = NOW()
    WHERE dental_transaction_id = ?
";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("si", $imagePath, $transaction_id);

if ($updateStmt->execute()) {
    $_SESSION['updateSuccess'] = "Your Dental Certificate request has been submitted successfully!";

    $notifySql = "
        INSERT INTO notifications (user_id, message, is_read, date_created)
        SELECT u.user_id,
            CONCAT('Patient #', ?, ' ', ?, ' has requested a Dental Certificate for transaction #', ?),
            0,
            NOW()
        FROM users u
        WHERE u.role = 'admin' AND u.branch_id = ?
    ";
    $notifyStmt = $conn->prepare($notifySql);
    $notifyStmt->bind_param("isii", $actual_patient_id, $patient_fullname, $transaction_id, $branch_id);
    $notifyStmt->execute();
    $notifyStmt->close();
} else {
    $_SESSION['updateError'] = "Database error. Please try again.";
}

$updateStmt->close();

header("Location: " . BASE_URL . "/Patient/pages/profile.php");
exit();
?>
