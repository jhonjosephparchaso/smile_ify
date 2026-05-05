<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid promo ID.']);
    exit;
}

$promo_id = intval($_GET['id']);

$sql = "
    SELECT 
        p.promo_id,
        p.name,
        p.image_path,
        p.description,
        p.discount_type,
        p.discount_value,
        MIN(bp.start_date) AS start_date,
        MAX(bp.end_date) AS end_date,
        GROUP_CONCAT(DISTINCT b.name ORDER BY b.name SEPARATOR ', ') AS branch_names,
        CASE 
            WHEN SUM(bp.status = 'Active') > 0 THEN 'Active'
            ELSE 'Inactive'
        END AS status
    FROM promo p
    LEFT JOIN branch_promo bp ON p.promo_id = bp.promo_id
    LEFT JOIN branch b ON bp.branch_id = b.branch_id
    WHERE p.promo_id = ?
    GROUP BY p.promo_id, p.name, p.image_path, p.description, p.discount_type, p.discount_value
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $promo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $row['description'] = $row['description'] ?: 'No description available.';
    $row['branch_names'] = $row['branch_names'] ?: 'Not specified';
    $row['start_date'] = $row['start_date'] ?: null;
    $row['end_date'] = $row['end_date'] ?: null;
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Promo not found.']);
}

$stmt->close();
$conn->close();
?>
