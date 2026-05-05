<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

$sql = "SELECT branch_id, name FROM branch ORDER BY name ASC";
$result = $conn->query($sql);

$branches = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $branches[] = [
            "branch_id" => $row["branch_id"],
            "name" => $row["name"]
        ];
    }
}

echo json_encode($branches);
?>