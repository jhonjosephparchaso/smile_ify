<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["count" => 0]);
    exit();
}

$branch_id = $_SESSION['branch_id'];

$sql = "SELECT COUNT(*) AS cnt
        FROM appointment_transaction a
        JOIN users u ON a.user_id = u.user_id
        WHERE a.branch_id = ?
            AND u.role = 'patient'
            AND a.appointment_date >= CURDATE()
            AND a.status = 'Booked'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$count = $row['cnt'] ?? 0;

header('Content-Type: application/json');
echo json_encode(["count" => $count]);
$conn->close();
?>
