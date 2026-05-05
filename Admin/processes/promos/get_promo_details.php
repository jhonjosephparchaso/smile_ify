<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$branch_id = $_SESSION['branch_id'];
$promo_id = $_GET['id'] ?? null;

if (!$promo_id) {
    echo json_encode(["error" => "Promo ID is required"]);
    exit();
}

$sql = "SELECT 
            p.promo_id,
            p.name,
            p.description,
            p.image_path,
            p.discount_type,
            p.discount_value,
            bp.start_date,
            bp.end_date,
            bp.status,
            p.date_created,
            p.date_updated
        FROM promo p
        INNER JOIN branch_promo bp ON p.promo_id = bp.promo_id
        WHERE bp.branch_id = ? AND p.promo_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit();
}

$stmt->bind_param("ii", $branch_id, $promo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Promo not found"]);
}

$stmt->close();
$conn->close();
