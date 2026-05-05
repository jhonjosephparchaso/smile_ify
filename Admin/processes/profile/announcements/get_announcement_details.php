<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No announcement ID provided"]);
    exit();
}

$announcementId = intval($_GET['id']);

$sql = "
    SELECT 
        a.announcement_id,
        a.title,
        a.description,
        a.type,
        ba.id AS branch_announcement_id,
        ba.branch_id,
        ba.status,
        ba.start_date,
        ba.end_date,
        ba.date_created,
        ba.date_updated
    FROM announcements a
    LEFT JOIN branch_announcements ba 
        ON a.announcement_id = ba.announcement_id
    WHERE a.announcement_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $announcementId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $row = array_map(fn($v) => $v === null ? '' : htmlspecialchars($v, ENT_QUOTES, 'UTF-8'), $row);
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Announcement not found"]);
}

$stmt->close();
$conn->close();
?>
