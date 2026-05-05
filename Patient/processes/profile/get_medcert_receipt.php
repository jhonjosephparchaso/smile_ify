<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

$transactionId = $_GET['id'] ?? '';

if (empty($transactionId)) {
    echo json_encode(["success" => false, "message" => "Invalid transaction ID"]);
    exit();
}

$sql = "SELECT medcert_receipt FROM dental_transaction WHERE dental_transaction_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transactionId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row && !empty($row['medcert_receipt'])) {
    echo json_encode([
        "success" => true,
        "file_path" => $row['medcert_receipt']
    ]);
} else {
    echo json_encode(["success" => false, "message" => "No receipt found"]);
}
