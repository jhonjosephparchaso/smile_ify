<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["data" => []]);
    exit();
}

$branch_id = $_SESSION['branch_id'] ?? null;
if (!$branch_id) {
    echo json_encode(["data" => [], "error" => "Branch not set"]);
    exit();
}

$sql = "SELECT 
            s.supply_id,
            s.name,
            bs.quantity,
            bs.reorder_level,
            bs.status
        FROM supply s
        INNER JOIN branch_supply bs ON s.supply_id = bs.supply_id
        WHERE bs.branch_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["data" => [], "error" => $conn->error]);
    exit();
}

$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();

$supplies = [];
while ($row = $result->fetch_assoc()) {
    $supplies[] = [
        $row['supply_id'],
        $row['name'],
        $row['quantity'],
        $row['reorder_level'],
        $row['status'],
        '<button class="btn-supply" data-type="supply" data-id="'.$row['supply_id'].'">Manage</button>'
    ];
}

echo json_encode(["data" => $supplies]);
$conn->close();
