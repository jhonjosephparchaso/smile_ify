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
            p.prescription_id,
            p.appointment_transaction_id,
            p.drug,
            p.dosage,
            p.frequency,
            p.duration
        FROM dental_prescription p
        WHERE p.appointment_transaction_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointmentID);
$stmt->execute();
$result = $stmt->get_result();

$prescriptions = [];
while ($row = $result->fetch_assoc()) {
    $prescriptions[] = [
        $row['prescription_id'],
        $row['drug'],
        $row['dosage'],
        $row['frequency'],
        $row['duration'],
        '<button class="btn-action" data-type="prescription" data-id="'.$row['prescription_id'].'">Manage</button>'
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $prescriptions]);
$conn->close();
