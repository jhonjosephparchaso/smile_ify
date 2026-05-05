<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$branchId = $_POST['branch_id'] ?? null;
$supplyId = $_POST['supply_id'] ?? null;

if (!$branchId) {
    echo json_encode(["error" => "No branch provided."]);
    exit;
}

$linkedServices = [];
if ($supplyId) {
    $stmtLinked = $conn->prepare("
        SELECT service_id, quantity_used 
        FROM service_supplies 
        WHERE supply_id = ?
    ");
    $stmtLinked->bind_param("i", $supplyId);
    $stmtLinked->execute();
    $resultLinked = $stmtLinked->get_result();
    while ($row = $resultLinked->fetch_assoc()) {
        $linkedServices[$row['service_id']] = $row['quantity_used'];
    }
    $stmtLinked->close();
}

$stmt = $conn->prepare("
    SELECT s.service_id, s.name, s.price, s.duration_minutes
    FROM service s
    INNER JOIN branch_service bs ON s.service_id = bs.service_id
    WHERE bs.branch_id = ? AND bs.status = 'Active'
    ORDER BY s.name ASC
");
$stmt->bind_param("i", $branchId);
$stmt->execute();
$result = $stmt->get_result();

$services = [];

while ($row = $result->fetch_assoc()) {
    $serviceId = (int) $row['service_id'];

    $services[] = [
        "id" => $serviceId,
        "name" => $row['name'],
        "price" => number_format((float)$row['price'], 2),
        "duration" => (int)$row['duration_minutes'],
        "assigned" => isset($linkedServices[$serviceId]),
        "quantity" => $linkedServices[$serviceId] ?? null
    ];
}

$stmt->close();
$conn->close();

echo json_encode($services);
?>
