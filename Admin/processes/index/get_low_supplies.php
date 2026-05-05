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
        SELECT s.name, bs.quantity, bs.reorder_level
        FROM branch_supply bs
        INNER JOIN supply s ON s.supply_id = bs.supply_id
        WHERE bs.branch_id = ? 
            AND bs.status = 'Available'
            AND bs.quantity <= bs.reorder_level
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branchId);
    $stmt->execute();
    $result = $stmt->get_result();

    $lowSupplies = [];
    while ($row = $result->fetch_assoc()) {
        $lowSupplies[] = [
            'name' => $row['name'],
            'quantity' => $row['quantity'],
        ];
    }

    echo json_encode(['lowSupplies' => $lowSupplies]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to fetch low supplies.',
        'details' => $e->getMessage()
    ]);
}
?>
