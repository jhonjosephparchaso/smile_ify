<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["data" => []]);
    exit;
}

$guardian_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        u.user_id,
        u.first_name,
        u.last_name,
        u.relationship,
        u.gender,
        u.status,
        u.date_started,
        (
            SELECT MAX(date_created) 
            FROM appointment_transaction 
            WHERE user_id = u.user_id
        ) AS recent_transaction
    FROM users u
    WHERE u.guardian_id = ?
    ORDER BY u.user_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $guardian_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {

    $fullName = htmlspecialchars($row['first_name'] . " " . $row['last_name']);

    $relationship = $row['relationship']
        ? htmlspecialchars($row['relationship'])
        : "-";

    $recent = $row['recent_transaction']
        ? date("Y-m-d h:i A", strtotime($row['recent_transaction']))
        : "<span style='color: #888;'>None</span>";

    $statusBadge = $row['status'] === "Active"
        ? "<span class='badge active-badge'>Active</span>"
        : "<span class='badge inactive-badge'>Inactive</span>";

    $actions = "
        <button class='btn-view' onclick=\"viewDependent({$row['user_id']})\">View</button>
    ";

    $createdDate = $row['date_started'] ? $row['date_started'] : "";

    $data[] = [
        $fullName,
        $relationship,
        $recent,
        $statusBadge,
        $actions,
        $createdDate
    ];
}

echo json_encode(["data" => $data]);
exit;
