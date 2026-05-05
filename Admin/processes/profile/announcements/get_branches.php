<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

$sql = "SELECT branch_id, name FROM branch WHERE status = 'Active' ORDER BY name ASC";
$result = $conn->query($sql);

$branches = [];
while ($row = $result->fetch_assoc()) {
    $branches[] = $row;
}

echo json_encode($branches);

$conn->close();
