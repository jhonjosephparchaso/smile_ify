<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No service ID provided"]);
    exit();
}

$branch_id = $_SESSION['branch_id'] ?? null;
$serviceId = intval($_GET['id']);

if (!$branch_id) {
    echo json_encode(["error" => "Branch not set"]);
    exit();
}

$sql = "SELECT 
            s.service_id,
            s.name,
            s.price,
            s.duration_minutes,
            s.requires_xray,
            bs.status,
            s.date_created,
            s.date_updated
        FROM service s
        INNER JOIN branch_service bs ON s.service_id = bs.service_id
        WHERE s.service_id = ? AND bs.branch_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $serviceId, $branch_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Service not found"]);
}

$conn->close();
