<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$response = [
    'newPatients' => 0,
    'totalPatients' => 0
];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION['branch_id'])) {
    echo json_encode(['error' => 'Branch not set in session']);
    exit;
}

$branchId = $_SESSION['branch_id'];

try {
    $startOfMonth = date('Y-m-01');
    $endOfMonth   = date('Y-m-t');

    $sqlNew = "SELECT COUNT(*) AS count
                FROM users
                WHERE role = 'patient'
                AND branch_id = ?
                AND DATE(date_created) BETWEEN ? AND ?";
    $stmt = $conn->prepare($sqlNew);
    $stmt->bind_param("iss", $branchId, $startOfMonth, $endOfMonth);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $response['newPatients'] = $result['count'] ?? 0;

    $sqlTotal = "SELECT COUNT(*) AS count
                    FROM users
                    WHERE role = 'patient'
                    AND branch_id = ?";
    $stmt = $conn->prepare($sqlTotal);
    $stmt->bind_param("i", $branchId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $response['totalPatients'] = $result['count'] ?? 0;

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to load patient counts',
        'details' => $e->getMessage()
    ]);
}
?>
