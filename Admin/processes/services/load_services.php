<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["data" => []]);
    exit();
}

$branch_id = $_SESSION['branch_id'];

$sql = "SELECT 
            s.service_id,
            s.name,
            s.price,
            s.duration_minutes,
            bs.status
        FROM service s
        INNER JOIN branch_service bs ON s.service_id = bs.service_id
        WHERE bs.branch_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();

$services = [];
while ($row = $result->fetch_assoc()) {
    $services[] = [
        $row['service_id'],
        $row['name'],
        $row['price'],
        $row['duration_minutes'],
        $row['status'],
        '<button class="btn-service" data-type="service" data-id="'.$row['service_id'].'">Manage</button>'
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $services]);
$conn->close();
