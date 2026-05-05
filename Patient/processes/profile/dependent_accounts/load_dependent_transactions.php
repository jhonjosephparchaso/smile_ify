<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(["data" => []]);
    exit;
}

$guardian_id = $_SESSION['user_id'];
$dependent_id = intval($_GET['user_id'] ?? 0);

$check = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND guardian_id = ?");
$check->bind_param("ii", $dependent_id, $guardian_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    echo json_encode(["data" => []]);
    exit;
}

$sql = "
    SELECT 
        dt.dental_transaction_id,
        b.name AS branch,
        GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS services,
        CONCAT('Dr. ', d.last_name, ', ', d.first_name, ' ', IFNULL(d.middle_name, '')) AS dentist,
        a.appointment_date,
        a.appointment_time,
        dt.total,
        dt.date_created
    FROM dental_transaction dt
    INNER JOIN appointment_transaction a 
        ON dt.appointment_transaction_id = a.appointment_transaction_id
    LEFT JOIN branch b 
        ON a.branch_id = b.branch_id
    LEFT JOIN dental_transaction_services dts 
        ON dts.dental_transaction_id = dt.dental_transaction_id
    LEFT JOIN service s 
        ON s.service_id = dts.service_id
    LEFT JOIN dentist d 
        ON d.dentist_id = COALESCE(dt.dentist_id, a.dentist_id)
    WHERE a.user_id = ?
        AND a.status = 'Completed'
    GROUP BY dt.dental_transaction_id
    ORDER BY dt.date_created DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dependent_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = [
        $row['dentist'] ?: '-',
        $row['branch'] ?: '-',
        $row['services'] ?: '-',
        $row['appointment_date'],
        substr($row['appointment_time'], 0, 5),
        number_format($row['total'], 2),
        '<button class="btn-action" data-type="transaction" data-id="'.$row['dental_transaction_id'].'">Manage</button>',
        $row['date_created']
    ];
}

echo json_encode(["data" => $transactions]);
$conn->close();
?>
