<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Transaction ID not provided or invalid']);
    exit;
}

$transactionId = intval($_GET['id']);

$sql = "
    SELECT 
        dt.dental_transaction_id,
        dt.appointment_transaction_id,
        dt.dentist_id,
        dt.promo_id,
        dt.payment_method,
        dt.cashless_receipt,
        dt.total,
        dt.additional_payment,
        dt.notes,
        dt.medcert_status,
        dt.fitness_status,
        dt.diagnosis,
        dt.remarks,
        dt.medcert_requested_date,
        dt.admin_user_id,
        dt.xray_file,
        dt.date_created,
        dt.date_updated,
        CONCAT(u.first_name, ' ', u.last_name) AS recorded_by,
        CASE WHEN DATE_ADD(dt.date_created, INTERVAL 7 DAY) >= NOW() THEN 1 ELSE 0 END AS medcert_eligible
    FROM dental_transaction dt
    LEFT JOIN users u ON dt.admin_user_id = u.user_id
    WHERE dt.dental_transaction_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transactionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Transaction not found']);
    exit;
}

$data = $result->fetch_assoc();
$stmt->close();

$servicesSql = "
    SELECT 
        s.service_id,
        s.name,
        s.price,
        dts.quantity,
        dts.additional_payment AS add_payment_per_services
    FROM dental_transaction_services dts
    LEFT JOIN service s ON dts.service_id = s.service_id
    WHERE dts.dental_transaction_id = ?
";

$servicesStmt = $conn->prepare($servicesSql);
$servicesStmt->bind_param("i", $transactionId);
$servicesStmt->execute();
$servicesResult = $servicesStmt->get_result();

$services = [];
while ($row = $servicesResult->fetch_assoc()) {
    $services[] = [
        'service_id' => (int)$row['service_id'],
        'name' => $row['name'],
        'price' => (float)$row['price'],
        'quantity' => (int)$row['quantity'],
        'add_payment_per_services' => (int)$row['add_payment_per_services']
    ];
}

$servicesStmt->close();

$response = [
    'dental_transaction_id' => (int)$data['dental_transaction_id'],
    'appointment_transaction_id' => (int)$data['appointment_transaction_id'],
    'dentist_id' => (int)$data['dentist_id'],
    'promo_id' => $data['promo_id'] ? (int)$data['promo_id'] : null,
    'payment_method' => $data['payment_method'] ?? '',
    'cashless_receipt' => $data['cashless_receipt'] ?? null,
    'total' => (float)$data['total'],
    'additional_payment' => (float)$data['additional_payment'],
    'notes' => $data['notes'] ?? '',
    'medcert_status' => $data['medcert_status'],
    'fitness_status' => $data['fitness_status'],
    'diagnosis' => $data['diagnosis'],
    'remarks' => $data['remarks'],
    'medcert_requested_date' => $data['medcert_requested_date'],
    'admin_user_id' => (int)$data['admin_user_id'],
    'recorded_by' => $data['recorded_by'] ?? 'Unknown',
    'date_created' => $data['date_created'],
    'date_updated' => !empty($data['date_updated']) ? $data['date_updated'] : '-',
    'medcert_eligible' => (int)$data['medcert_eligible'],
    'services' => $services,
    'xray_file' => $data['xray_file'] ?? null
];

echo json_encode($response);
exit;
