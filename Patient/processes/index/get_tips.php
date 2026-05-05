<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['tips' => []];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $sql = "SELECT tip_text FROM dental_tips ORDER BY RAND() LIMIT 5";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $response['tips'][] = htmlspecialchars($row['tip_text']);
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to load dental tips.',
        'details' => $e->getMessage()
    ]);
}

$conn->close();
