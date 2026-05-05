<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION['branch_id'])) {
    echo json_encode(['error' => 'Branch not set in session']);
    exit;
}

$branchId = $_SESSION['branch_id'];

try {
    $sql = "
        SELECT 
            bp.branch_promo_id,
            p.name AS promo_name, 
            COUNT(dt.promo_id) AS availed_count
        FROM dental_transaction dt
        INNER JOIN branch_promo bp ON dt.promo_id = bp.promo_id
        INNER JOIN promo p ON bp.promo_id = p.promo_id
        WHERE 
            bp.branch_id = ? 
            AND bp.status = 'Active'
        GROUP BY bp.branch_promo_id, p.promo_id
        ORDER BY availed_count DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branchId);
    $stmt->execute();
    $result = $stmt->get_result();

    $promos = [];
    while ($row = $result->fetch_assoc()) {
        $promos[] = [
            'branch_promo_id' => (int)$row['branch_promo_id'],
            'promo_name' => $row['promo_name'],
            'availed_count' => (int)$row['availed_count']
        ];
    }

    echo json_encode([
        'promos' => $promos,
        'total' => array_sum(array_column($promos, 'availed_count'))
    ]);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to fetch promo data.',
        'details' => $e->getMessage()
    ]);
}
?>
