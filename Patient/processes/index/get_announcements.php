<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['announcements' => []];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $sql = "
        SELECT 
            a.title,
            a.description,
            ba.start_date,
            ba.end_date,
            b.address AS branch_name
        FROM branch_announcements AS ba
        INNER JOIN announcements AS a ON ba.announcement_id = a.announcement_id
        INNER JOIN branch AS b ON ba.branch_id = b.branch_id
        WHERE ba.status = 'Active'
            AND (ba.end_date IS NULL OR ba.end_date >= CURDATE())
        ORDER BY ba.start_date ASC, ba.date_created DESC
        LIMIT 3
    ";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $response['announcements'][] = [
            'title' => htmlspecialchars($row['title']),
            'description' => htmlspecialchars($row['description']),
            'branch_name' => htmlspecialchars($row['branch_name']),
            'start_date' => $row['start_date'] ? date('F j, Y', strtotime($row['start_date'])) : '',
            'end_date' => $row['end_date'] ? date('F j, Y', strtotime($row['end_date'])) : ''
        ];
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to load announcements.',
        'details' => $e->getMessage()
    ]);
}

$conn->close();
