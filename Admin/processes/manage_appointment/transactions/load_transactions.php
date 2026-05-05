<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["data" => []]);
    exit();
}

$appointmentID = $_GET['appointment_id'] ?? null;
if (!$appointmentID || !is_numeric($appointmentID)) {
    echo json_encode(["data" => []]);
    exit();
}

$sql = "
    SELECT 
        t.dental_transaction_id,
        t.appointment_transaction_id,
        CONCAT('Dr. ', d.last_name, ', ', d.first_name, ' ', IFNULL(d.middle_name, '')) AS dentist,
        GROUP_CONCAT(s.name SEPARATOR ', ') AS services,
        t.total,
        t.payment_method
    FROM dental_transaction t
    LEFT JOIN dentist d ON t.dentist_id = d.dentist_id
    LEFT JOIN dental_transaction_services ts ON t.dental_transaction_id = ts.dental_transaction_id
    LEFT JOIN service s ON ts.service_id = s.service_id
    WHERE t.appointment_transaction_id = ?
    GROUP BY t.dental_transaction_id
    ORDER BY t.date_created DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointmentID);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = [
        $row['dental_transaction_id'],
        $row['dentist'] ?: '—',
        $row['services'] ?: '—',
        $row['payment_method'] ?: '—',
        number_format($row['total'], 2),
        '<button class="btn-action btn-primary" data-type="dental_transaction" data-id="'.$row['dental_transaction_id'].'">Manage</button>'
    ];
}

echo json_encode(["data" => $transactions]);

$conn->close();
