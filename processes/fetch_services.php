<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "
    SELECT 
        service_id,
        name,
        price,
        duration_minutes
    FROM service
    ORDER BY name ASC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'error' => $conn->error
    ]);
    exit;
}

$services = [];

while ($row = $result->fetch_assoc()) {
    $services[] = [
        'service_id'    => $row['service_id'],
        'service_name'  => $row['name'],
        'price'         => $row['price'],
        'duration'      => $row['duration_minutes'] . ' minutes',
    ];
}

echo json_encode([
    'success' => true,
    'services' => $services
]);
?>
