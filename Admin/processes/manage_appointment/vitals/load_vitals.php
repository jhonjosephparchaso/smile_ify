<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["data" => []]);
    exit();
}

$appointmentID = $_GET['appointment_id'] ?? null;
if (!$appointmentID || !is_numeric($appointmentID)) {
    echo json_encode(["data" => []]);
    exit();
}

$sql = "SELECT 
            v.vitals_id,
            v.body_temp,
            v.pulse_rate,
            v.blood_pressure
        FROM dental_vital v
        WHERE v.appointment_transaction_id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointmentID);
$stmt->execute();
$result = $stmt->get_result();

$vitals = [];
while ($row = $result->fetch_assoc()) {
    $vitals[] = [
        $row['vitals_id'],
        $row['body_temp'],
        $row['pulse_rate'],
        $row['blood_pressure'],
        '<button class="btn-action" data-type="vital" data-id="'.$row['vitals_id'].'">Manage</button>'
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $vitals]);
$conn->close();
