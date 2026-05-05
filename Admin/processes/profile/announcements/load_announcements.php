<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["data" => []]);
    exit();
}

if (!isset($_SESSION['branch_id'])) {
    echo json_encode(["error" => "Branch ID not found in session"]);
    exit();
}

$branchId = intval($_SESSION['branch_id']);

$sql = "
    SELECT 
        a.announcement_id,
        a.title,
        a.type,
        ba.start_date,
        ba.end_date,
        ba.status
    FROM announcements a
    INNER JOIN branch_announcements ba 
        ON a.announcement_id = ba.announcement_id
    WHERE ba.branch_id = ?
    ORDER BY ba.date_created DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branchId);
$stmt->execute();
$result = $stmt->get_result();

$announcements = [];
while ($row = $result->fetch_assoc()) {
    $announcements[] = [
        $row['announcement_id'],
        htmlspecialchars($row['title']),
        htmlspecialchars($row['type']),
        $row['start_date'] ?? '-',
        $row['end_date'] ?? '-',
        htmlspecialchars($row['status'] ?? 'Inactive'),
        '<button class="btn-announcement" data-type="announcement" data-id="'.$row['announcement_id'].'">Manage</button>'
    ];
}

echo json_encode(["data" => $announcements]);

$stmt->close();
$conn->close();
