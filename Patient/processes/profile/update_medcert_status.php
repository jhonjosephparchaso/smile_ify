<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$dentalTransactionId = $input['dental_transaction_id'] ?? null;

if (!$dentalTransactionId) {
    echo json_encode(['error' => 'Missing dental transaction ID']);
    exit();
}

$newStatus = 'Issued';

$sql = "UPDATE dental_transaction 
        SET medcert_status = ?, date_updated = NOW()
        WHERE dental_transaction_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newStatus, $dentalTransactionId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'new_status' => $newStatus]);
} else {
    echo json_encode(['error' => 'Database update failed']);
}

$stmt->close();
$conn->close();
?>
