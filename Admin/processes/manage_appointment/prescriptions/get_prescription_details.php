<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$prescriptionId = $_GET['id'] ?? null;
if (!$prescriptionId || !is_numeric($prescriptionId)) {
    echo json_encode(["error" => "Invalid ID"]);
    exit();
}

$sql = "
    SELECT 
        pr.prescription_id,
        pr.appointment_transaction_id,
        pr.drug,
        pr.route,
        pr.frequency,
        pr.dosage,
        pr.duration,
        pr.quantity,
        pr.instructions,
        pr.date_created,
        pr.date_updated,
        CONCAT('Dr. ', d.last_name, ', ', d.first_name, COALESCE(CONCAT(' ', d.middle_name), '')) AS dentist,
        CONCAT(u.last_name, ', ', u.first_name, COALESCE(CONCAT(' ', u.middle_name), '')) AS patient,
        CONCAT(auser.first_name, ' ', auser.last_name) AS recorded_by
    FROM dental_prescription pr
    LEFT JOIN appointment_transaction a ON pr.appointment_transaction_id = a.appointment_transaction_id
    LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
    LEFT JOIN users u ON a.user_id = u.user_id
    LEFT JOIN users auser ON pr.admin_user_id = auser.user_id
    WHERE pr.prescription_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $prescriptionId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["error" => "Prescription not found"]);
    exit();
}

echo json_encode($row);

$stmt->close();
$conn->close();
