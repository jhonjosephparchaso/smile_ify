<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(["data" => []]);
    exit();
}

$sql = "SELECT 
            u.user_id,
            CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name) AS name,
            b.nickname AS branch,
            u.status
        FROM users u
        LEFT JOIN branch b ON u.branch_id = b.branch_id
        WHERE u.role = 'admin'
        ORDER BY u.last_name ASC";

$result = $conn->query($sql);

$admins = [];
while ($row = $result->fetch_assoc()) {
    $admins[] = [
        $row['user_id'],
        $row['name'],
        ($row['branch'] ?: '-'),
        $row['status'],
        '<button class="btn-action" data-type="admin" data-id="'.$row['user_id'].'">Manage</button>',
        
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $admins]);
$conn->close();
