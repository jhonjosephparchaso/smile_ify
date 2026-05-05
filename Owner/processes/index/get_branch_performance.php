<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

try {
    $sql = "
        SELECT 
            b.branch_id,
            b.name AS name,
            COALESCE(SUM(
                dt.total +
                IFNULL(dt.additional_payment, 0) +
                IFNULL(dt.medcert_request_payment, 0)
            ), 0) AS total_revenue
        FROM branch b
        LEFT JOIN appointment_transaction at 
            ON b.branch_id = at.branch_id AND at.status = 'Completed'
        LEFT JOIN dental_transaction dt 
            ON at.appointment_transaction_id = dt.appointment_transaction_id
        GROUP BY b.branch_id
        ORDER BY total_revenue DESC
    ";

    $result = $conn->query($sql);
    $branches = [];

    while ($row = $result->fetch_assoc()) {
        $revenue = (float)$row['total_revenue'];

        if ($revenue > 0) {
            $branches[] = [
                'name' => $row['name'],
                'total_revenue' => $revenue
            ];
        }
    }

    echo json_encode(['branches' => $branches]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
