<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["data" => []]);
    exit();
}

$branch_id = $_SESSION['branch_id'];

$sql = "SELECT 
            p.promo_id,
            p.name,
            p.discount_type,
            p.discount_value,
            bp.start_date,
            bp.end_date,
            bp.status
        FROM promo p
        INNER JOIN branch_promo bp ON p.promo_id = bp.promo_id
        WHERE bp.branch_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();

$promos = [];
while ($row = $result->fetch_assoc()) {

    if ($row['discount_type'] === 'percentage') {
        $discount = rtrim(rtrim($row['discount_value'], '0'), '.') . '%';
    } else {
        $discount = '₱' . number_format($row['discount_value'], 0);
    }

    $start = !empty($row['start_date']) ? date("M d, Y", strtotime($row['start_date'])) : "-";
    $end   = !empty($row['end_date']) ? date("M d, Y", strtotime($row['end_date'])) : "-";
    $validity = $start . " - " . $end;

    $promos[] = [
        $row['promo_id'],
        $row['name'],
        $discount,
        $validity,
        $row['status'],
        '<button class="btn-promo" data-type="promo" data-id="'.$row['promo_id'].'">Manage</button>'
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $promos]);
$conn->close();
