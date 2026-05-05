<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['branch_id']) || !is_numeric($_GET['branch_id'])) {
    echo json_encode(['error' => 'Invalid branch ID']);
    exit;
}

$branch_id = intval($_GET['branch_id']);

try {
    $sql = "
        SELECT 
            ba.start_date,
            ba.end_date
        FROM branch_announcements ba
        JOIN announcements a ON a.announcement_id = ba.announcement_id
        WHERE ba.branch_id = ?
            AND ba.status = 'Active'
            AND (
                    LOWER(a.type) = 'closed'
                    OR LOWER(a.title) LIKE '%closed%'
                    OR LOWER(a.description) LIKE '%closed%'
                )
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $closedDates = [];

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['start_date']) && !empty($row['end_date'])) {
            $start = new DateTime($row['start_date']);
            $end = new DateTime($row['end_date']);
            while ($start <= $end) {
                $closedDates[] = $start->format('Y-m-d');
                $start->modify('+1 day');
            }
        } elseif (!empty($row['start_date'])) {
            $closedDates[] = $row['start_date'];
        }
    }

    $stmt->close();
    echo json_encode(['closedDates' => $closedDates]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
