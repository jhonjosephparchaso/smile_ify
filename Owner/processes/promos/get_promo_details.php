<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No promo ID provided"]);
    exit();
}

$promoId = intval($_GET['id']);

try {
    $sql = "SELECT 
                p.promo_id,
                p.name,
                p.description,
                p.image_path,
                p.discount_type,
                p.discount_value,
                p.date_created,
                p.date_updated,
                bp.start_date,
                bp.end_date
            FROM promo p
            LEFT JOIN branch_promo bp ON p.promo_id = bp.promo_id
            WHERE p.promo_id = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $promoId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$row = $result->fetch_assoc()) {
        echo json_encode(["error" => "Promo not found"]);
        exit();
    }

    $stmt->close();

    $branchSql = "SELECT branch_id FROM branch_promo WHERE promo_id = ?";
    $branchStmt = $conn->prepare($branchSql);
    if (!$branchStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $branchStmt->bind_param("i", $promoId);
    $branchStmt->execute();
    $branchResult = $branchStmt->get_result();

    $branches = [];
    while ($b = $branchResult->fetch_assoc()) {
        $branches[] = intval($b['branch_id']);
    }

    $branchStmt->close();

    $row['branches'] = $branches;

    echo json_encode($row);

} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

$conn->close();
?>
