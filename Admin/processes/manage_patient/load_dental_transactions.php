<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["data" => []]);
    exit();
}

$patientID = $_GET['id'] ?? null;
if (!$patientID || !is_numeric($patientID)) {
    echo json_encode(["data" => []]);
    exit();
}

$sql = "
    SELECT 
        dt.dental_transaction_id,
        b.name AS branch,
        GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS services,
        CONCAT('Dr. ', d.first_name, ' ', IF(d.middle_name IS NOT NULL AND d.middle_name != '', CONCAT(LEFT(d.middle_name, 1), '. '), ''), d.last_name) AS dentist,
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
        ON dts.service_id = s.service_id
    LEFT JOIN dentist d 
        ON d.dentist_id = COALESCE(dt.dentist_id, a.dentist_id)
    WHERE a.user_id = ?
    GROUP BY dt.dental_transaction_id
    ORDER BY dt.date_created DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = [
        $row['dental_transaction_id'] ?: '-',
        $row['dentist'] ?: '-',
        $row['services'] ?: '-',
        $row['appointment_date'] ?: '-',
        $row['appointment_time'] ? substr($row['appointment_time'], 0, 5) : '-',
        number_format((float)$row['total'], 2),
        '<button class="btn-action" data-type="transaction" data-id="'.$row['dental_transaction_id'].'">Manage</button>',
        $row['date_created'] ? date("F j, Y", strtotime($row['date_created'])) : '-'
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $transactions]);
$conn->close();
?>
