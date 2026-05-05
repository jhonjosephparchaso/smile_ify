<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

$sql = "SELECT service_id, name FROM service ORDER BY name ASC";
$result = $conn->query($sql);

$services = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = [
            "service_id" => $row["service_id"],
            "name" => $row["name"]
        ];
    }
}

echo json_encode($services);
