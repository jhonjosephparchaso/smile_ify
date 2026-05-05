<?php
ob_start();
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['transaction_id'])) {
    echo json_encode(["success" => false, "error" => "No transaction ID provided"]);
    exit;
}

$transactionId = intval($data['transaction_id']);

$stmt = $conn->prepare("UPDATE dental_transaction SET prescription_downloaded = 1 WHERE dental_transaction_id = ?");
$stmt->bind_param("i", $transactionId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "DB update failed"]);
}
