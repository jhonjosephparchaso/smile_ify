<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

try {
    $currentYear = date('Y');
    $currentMonth = date('m');
    $monthStart = date("$currentYear-" . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . "-01");
    $monthEnd = date("Y-m-t", strtotime($monthStart));

    $branchesQuery = "SELECT branch_id, name FROM branch WHERE status IN ('active', 'inactive') ORDER BY branch_id";
    $branchesResult = $conn->query($branchesQuery);
    
    $allBranches = [];
    while ($b = $branchesResult->fetch_assoc()) {
        $allBranches[] = $b;
    }

    $sqlCurrent = "
        SELECT COALESCE(SUM(
            dt.total +
            IFNULL(dt.additional_payment, 0) +
            IFNULL(dt.medcert_request_payment, 0)
        ), 0) AS current_revenue
        FROM appointment_transaction at
        LEFT JOIN dental_transaction dt
            ON at.appointment_transaction_id = dt.appointment_transaction_id
        WHERE at.branch_id IS NOT NULL
        AND DATE(at.appointment_date) BETWEEN ? AND ?
        AND at.status = 'Completed'
    ";
    
    $stmt = $conn->prepare($sqlCurrent);
    $stmt->bind_param("ss", $monthStart, $monthEnd);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentRevenue = ($result->fetch_assoc())['current_revenue'] ?? 0;
    $stmt->close();

    $branches = [];
    foreach ($allBranches as $branch) {
        $sql = "
            SELECT COALESCE(SUM(
                dt.total +
                IFNULL(dt.additional_payment, 0) +
                IFNULL(dt.medcert_request_payment, 0)
            ), 0) AS revenue
            FROM appointment_transaction AS at
            LEFT JOIN dental_transaction AS dt
                ON at.appointment_transaction_id = dt.appointment_transaction_id
            WHERE at.branch_id = ?
            AND DATE(at.appointment_date) BETWEEN ? AND ?
            AND at.status = 'Completed'
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $branch['branch_id'], $monthStart, $monthEnd);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $revenue = (float)$row['revenue'];
        $stmt->close();

        if ($revenue > 0) {
            $branches[] = [
                'branch_id' => $branch['branch_id'],
                'name' => $branch['name'],
                'total_revenue' => $revenue
            ];
        }
    }

    usort($branches, function ($a, $b) {
        return $b['total_revenue'] <=> $a['total_revenue'];
    });

    echo json_encode([
        'current_revenue' => (float)$currentRevenue,
        'branches' => $branches
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
