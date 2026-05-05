<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

try {
    $sql = "
        SELECT 
            b.name AS name,
            COUNT(a.appointment_transaction_id) AS total_booked
        FROM branch b
        LEFT JOIN appointment_transaction a 
            ON b.branch_id = a.branch_id 
            AND a.status = 'booked'
            AND YEAR(a.appointment_date) = YEAR(CURDATE())
            AND MONTH(a.appointment_date) = MONTH(CURDATE())
        GROUP BY b.branch_id
        ORDER BY b.name
    ";

    $result = $conn->query($sql);
    $overview = [];

    while ($row = $result->fetch_assoc()) {
        $overview[] = [
            'name'   => $row['name'],
            'total_booked'  => (int)$row['total_booked']
        ];
    }

    echo json_encode(['overview' => $overview]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
