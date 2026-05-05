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
    echo json_encode(["error" => "No service ID provided"]);
    exit();
}

$serviceId = intval($_GET['id']);

try {
    $sql = "SELECT 
                s.service_id,
                s.name,
                s.price,
                s.duration_minutes,
                s.requires_xray,
                s.date_created,
                s.date_updated
            FROM service s
            LEFT JOIN branch_service bs ON s.service_id = bs.service_id
            WHERE s.service_id = ?
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$row = $result->fetch_assoc()) {
        echo json_encode(["error" => "Service not found"]);
        exit();
    }

    $stmt->close();

    $branchSql = "SELECT branch_id FROM branch_service WHERE service_id = ?";
    $branchStmt = $conn->prepare($branchSql);
    if (!$branchStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $branchStmt->bind_param("i", $serviceId);
    $branchStmt->execute();
    $branchResult = $branchStmt->get_result();

    $branches = [];
    while ($b = $branchResult->fetch_assoc()) {
        $branches[] = intval($b['branch_id']);
    }

    $branchStmt->close();

    $row['branches'] = $branches;

    echo json_encode($row);
} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

$conn->close();
