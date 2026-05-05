<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$appointmentId = (int)($_POST['appointment_id'] ?? 0);

if ($appointmentId <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid appointment reference."]);
    exit;
}

$infoSql = "
    SELECT 
        at.appointment_transaction_id,
        at.branch_id,
        at.user_id AS patient_id
    FROM appointment_transaction at
    JOIN users u ON u.user_id = at.user_id
    WHERE at.appointment_transaction_id = ?
        AND at.status = 'Pending Reschedule'
        AND (
                at.user_id = ?
                OR u.guardian_id = ?
        )
    LIMIT 1
";

$infoStmt = $conn->prepare($infoSql);
$infoStmt->bind_param("iii", $appointmentId, $userId, $userId);
$infoStmt->execute();
$appointment = $infoStmt->get_result()->fetch_assoc();

if (!$appointment) {
    echo json_encode(["success" => false, "message" => "Unable to cancel appointment."]);
    exit;
}

$cancelSql = "
    UPDATE appointment_transaction
    SET status = 'Cancelled',
        date_updated = NOW()
    WHERE appointment_transaction_id = ?
";

$cancelStmt = $conn->prepare($cancelSql);
$cancelStmt->bind_param("i", $appointmentId);
$cancelStmt->execute();

$branchId  = (int) $appointment['branch_id'];
$patientId = (int) $appointment['patient_id'];

$guardianStmt = $conn->prepare("
    SELECT guardian_id
    FROM users
    WHERE user_id = ?
    LIMIT 1
");
$guardianStmt->bind_param("i", $patientId);
$guardianStmt->execute();
$userInfo = $guardianStmt->get_result()->fetch_assoc();

$guardianId = $userInfo['guardian_id'] ?? null;

$admins = $conn->prepare("
    SELECT user_id
    FROM users
    WHERE role = 'admin'
        AND branch_id = ?
");
$admins->bind_param("i", $branchId);
$admins->execute();
$adminResult = $admins->get_result();

$notifSql = "
    INSERT INTO notifications (user_id, message, is_read, date_created)
    VALUES (?, ?, 0, NOW())
";
$notifStmt = $conn->prepare($notifSql);

$message = "Appointment #{$appointmentId} has been cancelled by Patient #{$patientId}.";

while ($admin = $adminResult->fetch_assoc()) {
    $notifStmt->bind_param("is", $admin['user_id'], $message);
    $notifStmt->execute();
}

if (!empty($guardianId)) {
    $userMessage = "The appointment for your dependent has been cancelled.";
    $notifStmt->bind_param("is", $guardianId, $userMessage);
    $notifStmt->execute();
} else {
    $userMessage = "Your appointment has been cancelled.";
    $notifStmt->bind_param("is", $patientId, $userMessage);
    $notifStmt->execute();
}

echo json_encode(["success" => true]);
