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

$input = json_decode(file_get_contents('php://input'), true);
$dentalTransactionId = $input['dental_transaction_id'] ?? null;
$newStatus = $input['new_status'] ?? null;

if (!$dentalTransactionId || !$newStatus) {
    echo json_encode(['error' => 'Missing required data']);
    exit();
}

$allowedStatuses = ['None', 'Eligible', 'Issued', 'Expired', 'Requested'];
if (!in_array($newStatus, $allowedStatuses)) {
    echo json_encode(['error' => 'Invalid status value']);
    exit();
}

$sql = "UPDATE dental_transaction 
        SET medcert_status = ?, date_updated = NOW() 
        WHERE dental_transaction_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newStatus, $dentalTransactionId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Database update failed']);
}

$stmt->close();
$conn->close();
?>
