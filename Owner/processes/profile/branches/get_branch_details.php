<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No branch ID provided"]);
    exit();
}

$branchId = intval($_GET['id']);

$sql = "SELECT 
            branch_id,
            name,
            nickname,
            address,
            dental_chairs,
            phone_number,
            map_url,
            status,
            date_updated,
            date_created
        FROM branch
        WHERE branch_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => $conn->error]);
    exit();
}

$stmt->bind_param("i", $branchId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Branch not found"]);
}

$conn->close();
