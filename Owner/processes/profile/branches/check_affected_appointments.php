<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$branch_id = intval($_GET['branch_id'] ?? 0);

if ($branch_id <= 0) {
    echo json_encode(['error' => 'Invalid branch']);
    exit;
}

$sql = "
    SELECT COUNT(*) AS total
    FROM appointment_transaction
    WHERE branch_id = ?
        AND status = 'Booked'
        AND appointment_date >= CURDATE()
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode([
    'count' => (int)($result['total'] ?? 0)
]);
