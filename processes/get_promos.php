<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "
    SELECT 
        p.promo_id,
        p.name,
        p.image_path,
        MIN(bp.start_date) AS start_date,
        MAX(bp.end_date) AS end_date
    FROM promo p
    LEFT JOIN branch_promo bp 
        ON p.promo_id = bp.promo_id AND bp.status = 'Active'
    WHERE bp.status = 'Active'
        AND p.image_path IS NOT NULL
        AND p.image_path != ''
    GROUP BY p.promo_id
    ORDER BY p.date_updated DESC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => $conn->error]);
    exit;
}

$promos = [];
while ($row = $result->fetch_assoc()) {
    $row['image_path'] = trim($row['image_path']);
    $promos[] = $row;
}

echo json_encode($promos);
$conn->close();
?>
