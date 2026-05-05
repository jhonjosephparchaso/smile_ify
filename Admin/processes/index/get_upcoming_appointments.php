<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$response = [
    'today' => 0,
    'thisWeek' => 0,
    'thisMonth' => 0
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
    $today = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $weekEnd   = date('Y-m-d', strtotime('sunday this week'));
    $monthStart = date('Y-m-01');
    $monthEnd   = date('Y-m-t');

    $sqlToday = "SELECT COUNT(*) AS count 
                    FROM appointment_transaction 
                    WHERE DATE(appointment_date) = ? 
                    AND branch_id = ? 
                    AND status = 'Booked'";
    $stmt = $conn->prepare($sqlToday);
    $stmt->bind_param("si", $today, $branchId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $response['today'] = $result['count'] ?? 0;

    $sqlWeek = "SELECT COUNT(*) AS count 
                FROM appointment_transaction 
                WHERE DATE(appointment_date) BETWEEN ? AND ? 
                AND branch_id = ? 
                AND status = 'Booked'";
    $stmt = $conn->prepare($sqlWeek);
    $stmt->bind_param("ssi", $weekStart, $weekEnd, $branchId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $response['thisWeek'] = $result['count'] ?? 0;

    $sqlMonth = "SELECT COUNT(*) AS count 
                    FROM appointment_transaction 
                    WHERE DATE(appointment_date) BETWEEN ? AND ? 
                    AND branch_id = ? 
                    AND status = 'Booked'";
    $stmt = $conn->prepare($sqlMonth);
    $stmt->bind_param("ssi", $monthStart, $monthEnd, $branchId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $response['thisMonth'] = $result['count'] ?? 0;

    $stmt->close();

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to fetch data.',
        'details' => $e->getMessage()
    ]);
}
?>
