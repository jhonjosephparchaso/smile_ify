<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(["data" => []]);
    exit();
}

$totalBranches = $conn->query("SELECT COUNT(*) AS total FROM branch")->fetch_assoc()['total'];

$sql = "
    SELECT 
        s.service_id,
        s.name,
        s.price,
        s.duration_minutes,
        GROUP_CONCAT(DISTINCT b.nickname ORDER BY b.nickname SEPARATOR ', ') AS branches
    FROM service s
    LEFT JOIN branch_service bs ON s.service_id = bs.service_id
    LEFT JOIN branch b ON b.branch_id = bs.branch_id
    GROUP BY s.service_id, s.name, s.price, s.duration_minutes
    ORDER BY s.service_id ASC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Query failed: " . $conn->error]);
    exit();
}

$services = [];
while ($row = $result->fetch_assoc()) {

    $assignedBranches = array_map('trim', explode(',', $row['branches']));

    if (!empty($row['branches']) && count($assignedBranches) == $totalBranches) {
        $branchList = "All Branches";
    } else {
        $branchList = $row['branches'] ?: '-';
    }

    $services[] = [
        htmlspecialchars($row['name']),
        htmlspecialchars($branchList),
        number_format($row['price'], 0),
        $row['duration_minutes'],
        '<button class="btn-service" data-id="'.$row['service_id'].'">Manage</button>'
    ];
}

echo json_encode(["data" => $services]);
$conn->close();
?>
