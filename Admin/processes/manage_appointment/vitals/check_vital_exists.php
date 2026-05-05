<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$appointment_id = intval($_GET['appointment_id'] ?? 0);
$response = ['exists' => false];

if ($appointment_id > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM dental_vital WHERE appointment_transaction_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $response['exists'] = true;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
