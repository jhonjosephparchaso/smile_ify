<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/Admin/pages/patients.php");
    exit();
}

$transaction_id  = $_POST['dental_transaction_id'] ?? null;
$fitness_status  = trim($_POST['fitness_status'] ?? '');
$diagnosis       = trim($_POST['diagnosis'] ?? '');
$remarks         = trim($_POST['remarks'] ?? '');
$payment_method  = $_POST['payment_method'] ?? null;
$medcert_notes   = trim($_POST['medcert_notes'] ?? '');

$getPatient = $conn->prepare("
    SELECT at.user_id, at.appointment_date, at.appointment_time, u.first_name, u.middle_name, u.last_name, at.branch_id,
            dt.medcert_receipt
    FROM dental_transaction dt
    JOIN appointment_transaction at ON at.appointment_transaction_id = dt.appointment_transaction_id
    JOIN users u ON u.user_id = at.user_id
    WHERE dt.dental_transaction_id = ?
");
$getPatient->bind_param("i", $transaction_id);
$getPatient->execute();
$result = $getPatient->get_result();
$patientData = $result->fetch_assoc();
$getPatient->close();

if (!$patientData) {
    $_SESSION['updateError'] = "Unable to find patient for this transaction.";
    header("Location: " . BASE_URL . "/Admin/pages/patients.php");
    exit();
}

$patientId = $patientData['user_id'];
$appointmentDate = date("F j, Y", strtotime($patientData['appointment_date']));
$appointmentTime = date("g:i A", strtotime($patientData['appointment_time']));
$branchId = $patientData['branch_id'];
$existingReceipt = $patientData['medcert_receipt'];

$last_name_clean = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($patientData['last_name']));
$middle_initial = $patientData['middle_name'] ? strtoupper(substr($patientData['middle_name'], 0, 1)) . '. ' : '';
$patient_fullname = $patientData['first_name'] . ' ' . $middle_initial . $patientData['last_name'];

$receiptPath = $existingReceipt;

if (empty($existingReceipt) && $payment_method === 'cashless' && isset($_FILES['receipt_upload']) && $_FILES['receipt_upload']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['receipt_upload']['tmp_name'];
    $fileExt = strtolower(pathinfo($_FILES['receipt_upload']['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($fileExt, $allowedTypes)) {
        $_SESSION['updateError'] = "Invalid file type. Allowed: JPG, PNG, WEBP.";
        header("Location: " . BASE_URL . "/Admin/pages/manage_patient.php?id=" . urlencode($patientId) . "&tab=dental_transaction");
        exit();
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/images/payments/medcert_payments/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = $transaction_id . "_" . $last_name_clean . "." . $fileExt;
    $targetPath = $uploadDir . $fileName;

    $oldFiles = glob($uploadDir . $transaction_id . "_*.*");
    foreach ($oldFiles as $oldFile) {
        if (is_file($oldFile)) unlink($oldFile);
    }

    if (!move_uploaded_file($fileTmpPath, $targetPath)) {
        $_SESSION['updateError'] = "Failed to upload receipt file.";
        header("Location: " . BASE_URL . "/Admin/pages/manage_patient.php?id=" . urlencode($patientId) . "&tab=dental_transaction");
        exit();
    }

    $receiptPath = "/images/payments/medcert_payments/" . $fileName;
}

$priceStmt = $conn->prepare("SELECT price FROM service WHERE name = 'Dental Certificate' LIMIT 1");
$priceStmt->execute();
$priceStmt->bind_result($medcertPayment);
$priceStmt->fetch();
$priceStmt->close();
if (!$medcertPayment) $medcertPayment = 150;

$updateSql = "
    UPDATE dental_transaction
    SET fitness_status = ?,
        diagnosis = ?,
        remarks = ?,
        medcert_notes = ?,
        medcert_status = 'Eligible',
        medcert_requested_date = NOW(),
        medcert_request_payment = ?,
        medcert_receipt = ?,
        date_updated = NOW()
    WHERE dental_transaction_id = ?
";
$stmt = $conn->prepare($updateSql);
$stmt->bind_param("ssssisi", $fitness_status, $diagnosis, $remarks, $medcert_notes, $medcertPayment, $receiptPath, $transaction_id);


if ($stmt->execute()) {
    $message = "Your Dental Certificate request from your appointment on $appointmentDate at $appointmentTime has been approved.";
    $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notif->bind_param("is", $patientId, $message);
    $notif->execute();
    $notif->close();

    $_SESSION['updateSuccess'] = "Dental Certificate verified successfully and patient notified.";
} else {
    $_SESSION['updateError'] = "Error verifying Dental Certificate: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: " . BASE_URL . "/Admin/pages/manage_patient.php?id=" . urlencode($patientId) . "&tab=dental_transaction");
exit();
?>
