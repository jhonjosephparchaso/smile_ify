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
$branchId      = (int)($_POST['branch_id'] ?? 0);
$dentistId     = (int)($_POST['dentist_id'] ?? 0);
$date          = $_POST['appointment_date'] ?? '';
$time          = $_POST['appointment_time'] ?? '';

if ($appointmentId <= 0 || $branchId <= 0 || $dentistId <= 0 || !$date || !$time) {
    echo json_encode(["success" => false, "message" => "Invalid reschedule data."]);
    exit;
}

$conn->begin_transaction();

$infoSql = "
    SELECT 
        appointment_transaction_id,
        user_id AS patient_id
    FROM appointment_transaction
    WHERE appointment_transaction_id = ?
        AND (
            user_id = ?
            OR user_id IN (
                SELECT user_id FROM users WHERE guardian_id = ?
            )
        )
    FOR UPDATE
";
$infoStmt = $conn->prepare($infoSql);
$infoStmt->bind_param("iii", $appointmentId, $userId, $userId);
$infoStmt->execute();
$appointment = $infoStmt->get_result()->fetch_assoc();

if (!$appointment) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Appointment no longer available."]);
    exit;
}

$updateSql = "
    UPDATE appointment_transaction
    SET
        branch_id = ?,
        dentist_id = ?,
        appointment_date = ?,
        appointment_time = ?,
        status = 'Booked',
        date_updated = NOW()
    WHERE appointment_transaction_id = ?
";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param(
    "iissi",
    $branchId,
    $dentistId,
    $date,
    $time,
    $appointmentId
);
$updateStmt->execute();

if ($updateStmt->affected_rows === 0) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "No changes were applied."]);
    exit;
}

$patientId = (int)$appointment['patient_id'];

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

$adminMessage = "Appointment #{$appointmentId} has been rescheduled and confirmed by the patient.";

while ($admin = $adminResult->fetch_assoc()) {
    $notifStmt->bind_param("is", $admin['user_id'], $adminMessage);
    $notifStmt->execute();
}

$userMessage = "Your appointment has been successfully rescheduled and confirmed.";

$targetUser = $guardianId ?: $patientId;
$notifStmt->bind_param("is", $targetUser, $userMessage);
$notifStmt->execute();

$conn->commit();

echo json_encode(["success" => true]);
