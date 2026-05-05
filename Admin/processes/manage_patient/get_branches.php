<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$stmt = $conn->prepare("
    SELECT nickname
    FROM branch
    WHERE status = 'Active'
    ORDER BY nickname ASC
");
$stmt->execute();
$result = $stmt->get_result();

$branches = [];
while ($row = $result->fetch_assoc()) {
    $branches[] = $row['nickname'];
}

echo json_encode(["branches" => $branches]);

$stmt->close();
$conn->close();
