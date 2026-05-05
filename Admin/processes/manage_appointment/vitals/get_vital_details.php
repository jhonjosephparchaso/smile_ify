<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$vitalId = $_GET['id'] ?? null;
if (!$vitalId || !is_numeric($vitalId)) {
    echo json_encode(['error' => 'Invalid vital ID']);
    exit();
}

$sql = "
    SELECT 
        v.vitals_id,
        v.appointment_transaction_id,
        v.body_temp,
        v.pulse_rate,
        v.respiratory_rate,
        v.blood_pressure,
        v.height,
        v.weight,
        v.is_swelling,
        v.is_bleeding,
        v.is_sensitive,
        v.date_created,
        v.date_updated,
        CONCAT('Dr. ', COALESCE(d.last_name, ''), ', ', COALESCE(d.first_name, ''), ' ', COALESCE(d.middle_name, '')) AS dentist,
        CONCAT(u.last_name, ', ', u.first_name, COALESCE(CONCAT(' ', u.middle_name), '')) AS patient,
        CONCAT(auser.first_name, ' ', auser.last_name) AS recorded_by
    FROM dental_vital v
    LEFT JOIN appointment_transaction a ON v.appointment_transaction_id = a.appointment_transaction_id
    LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
    LEFT JOIN users u ON a.user_id = u.user_id
    LEFT JOIN users auser ON v.admin_user_id = auser.user_id
    WHERE v.vitals_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vitalId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo json_encode(['error' => 'Vital record not found']);
    exit();
}

echo json_encode($data);

$stmt->close();
$conn->close();
