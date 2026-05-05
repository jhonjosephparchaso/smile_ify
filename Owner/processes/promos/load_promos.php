<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(["data" => []]);
    exit();
}

$totalBranches = $conn->query("SELECT COUNT(*) AS total FROM branch")->fetch_assoc()['total'];

$sql = "
    SELECT 
        p.promo_id, 
        p.name, 
        p.discount_type, 
        p.discount_value,
        GROUP_CONCAT(DISTINCT b.nickname ORDER BY b.nickname SEPARATOR ', ') AS branches,
        MIN(bp.start_date) AS start_date, 
        MAX(bp.end_date) AS end_date
    FROM promo p
    LEFT JOIN branch_promo bp ON p.promo_id = bp.promo_id
    LEFT JOIN branch b ON b.branch_id = bp.branch_id
    GROUP BY p.promo_id, p.name, p.discount_type, p.discount_value
    ORDER BY p.promo_id ASC
";

$result = $conn->query($sql);

$promos = [];
while ($row = $result->fetch_assoc()) {

    $discount = ($row['discount_type'] === 'percentage')
        ? rtrim(rtrim($row['discount_value'], '0'), '.') . '%'
        : '₱' . number_format($row['discount_value'], 0);

    $validity = (!empty($row['start_date']) && !empty($row['end_date']))
        ? date("M d, Y", strtotime($row['start_date'])) . " - " . date("M d, Y", strtotime($row['end_date']))
        : '-';

    $assignedBranches = array_map('trim', explode(',', $row['branches']));

    if (count($assignedBranches) == $totalBranches) {
        $branchList = "All Branches";
    } else {
        $branchList = $row['branches'] ?: '-';
    }

    $promos[] = [
        htmlspecialchars($row['name']),
        htmlspecialchars($branchList),
        $discount,
        $validity,
        '<button class="btn-promo" data-id="' . $row['promo_id'] . '">Manage</button>'
    ];
}

echo json_encode(["data" => $promos]);
$conn->close();
?>
