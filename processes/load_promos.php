<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

try {
    if (!isset($conn)) {
        throw new Exception("Database connection not initialized.");
    }

    $branchId = $_SESSION['branch_id'] ?? $_GET['branch_id'] ?? null;

    if (!$branchId) {
        echo json_encode(['error' => 'Missing branch ID']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT 
            p.promo_id AS id,
            p.name,
            p.description,
            p.discount_type,
            p.discount_value,
            bp.start_date,
            bp.end_date,
            bp.status
        FROM branch_promo bp
        INNER JOIN promo p ON bp.promo_id = p.promo_id
        WHERE bp.branch_id = ?
            AND bp.status = 'Active'
            AND (bp.start_date IS NULL OR bp.start_date <= NOW())
            AND (bp.end_date IS NULL OR bp.end_date >= NOW())
        ORDER BY p.name ASC
    ");
    $stmt->bind_param("i", $branchId);
    $stmt->execute();
    $result = $stmt->get_result();

    $promos = [];
    while ($row = $result->fetch_assoc()) {
        $promos[] = $row;
    }

    echo json_encode($promos);

    $stmt->close();
    $conn->close();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => basename($e->getFile()) . ':' . $e->getLine()
    ]);
}
