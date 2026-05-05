<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$dentist_id = intval($_GET['dentist_id'] ?? 0);
if ($dentist_id <= 0) {
    echo json_encode(['error' => 'Invalid dentist']);
    exit;
}

$sql = "
    SELECT COUNT(*) AS total
    FROM appointment_transaction
    WHERE dentist_id = ?
        AND status = 'Booked'
        AND appointment_date >= CURDATE()
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dentist_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode([
    'count' => (int)$row['total']
]);
