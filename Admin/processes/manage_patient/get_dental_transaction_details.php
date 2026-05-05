<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$transactionId = $_GET['id'] ?? null;
if (!$transactionId || !is_numeric($transactionId)) {
    echo json_encode(['error' => 'Invalid transaction ID']);
    exit();
}

$sql = "
    SELECT 
        dt.dental_transaction_id,
        dt.appointment_transaction_id,
        dt.dentist_id,
        dt.admin_user_id,
        dt.promo_id,
        dt.promo_name,
        dt.promo_type,
        dt.promo_value,
        dt.payment_method,
        dt.cashless_receipt,
        dt.xray_file,
        dt.total,
        dt.additional_payment,
        dt.notes,
        dt.medcert_status,
        dt.fitness_status,
        dt.diagnosis,
        dt.remarks,
        dt.medcert_notes,
        dt.medcert_requested_date,
        dt.date_created,
        dt.date_updated,
        dt.prescription_downloaded,

        CONCAT(ua.first_name, ' ', ua.last_name) AS admin_name,

        a.appointment_transaction_id,
        a.appointment_date,
        a.appointment_time,

        b.name AS branch,

        d.last_name AS dentist_last_name,
        d.first_name AS dentist_first_name,
        d.middle_name AS dentist_middle_name,
        CONCAT('Dr. ', d.first_name, ' ', IF(d.middle_name IS NOT NULL AND d.middle_name != '', CONCAT(LEFT(d.middle_name, 1), '. '), ''), d.last_name) AS dentist_name,
        d.license_number,
        d.license_number_iv,
        d.license_number_tag,
        d.signature_image,

        dv.vitals_id,
        dv.body_temp,
        dv.pulse_rate,
        dv.respiratory_rate,
        dv.blood_pressure,
        dv.height,
        dv.weight,
        dv.is_swelling,
        dv.is_bleeding,
        dv.is_sensitive,

        u.last_name AS patient_last_name,
        u.first_name AS patient_first_name,
        u.middle_name AS patient_middle_name,
        u.date_of_birth AS patient_dob,
        u.date_of_birth_iv AS patient_dob_iv,
        u.date_of_birth_tag AS patient_dob_tag,
        u.gender AS patient_gender

    FROM dental_transaction dt
    INNER JOIN appointment_transaction a 
        ON dt.appointment_transaction_id = a.appointment_transaction_id
    LEFT JOIN branch b 
        ON a.branch_id = b.branch_id
    LEFT JOIN dentist d 
        ON d.dentist_id = COALESCE(dt.dentist_id, a.dentist_id)
    LEFT JOIN dental_vital dv 
        ON dv.appointment_transaction_id = a.appointment_transaction_id
    LEFT JOIN users u 
        ON a.user_id = u.user_id
    LEFT JOIN users ua 
        ON ua.user_id = dt.admin_user_id
    WHERE dt.dental_transaction_id = ?
    GROUP BY dt.dental_transaction_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transactionId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo json_encode(['error' => 'Transaction not found']);
    exit();
}

if (!empty($data['license_number']) && !empty($data['license_number_iv']) && !empty($data['license_number_tag'])) {
    $data['license_number'] = decryptField(
        $data['license_number'],
        $data['license_number_iv'],
        $data['license_number_tag'],
        $ENCRYPTION_KEY
    );
}

if (!empty($data['patient_dob']) && !empty($data['patient_dob_iv']) && !empty($data['patient_dob_tag'])) {
    $data['patient_dob'] = decryptField(
        $data['patient_dob'],
        $data['patient_dob_iv'],
        $data['patient_dob_tag'],
        $ENCRYPTION_KEY
    );
}

$servicesSql = "
    SELECT 
        dts.service_name,
        dts.quantity,
        dts.service_price,
        dts.additional_payment
    FROM dental_transaction_services dts
    WHERE dts.dental_transaction_id = ?
";
$stmtServices = $conn->prepare($servicesSql);
$stmtServices->bind_param("i", $transactionId);
$stmtServices->execute();
$resServices = $stmtServices->get_result();

$services = [];
$serviceStrings = [];
while ($row = $resServices->fetch_assoc()) {
    $row['subtotal'] = ($row['quantity'] * $row['service_price']) + $row['additional_payment'];
    $services[] = $row;
    $serviceStrings[] = $row['service_name'] . " × " . $row['quantity'];
}
$data['services_raw'] = $services;
$data['services'] = $services;
$data['services_text'] = implode("\n", $serviceStrings);

if (!empty($data['promo_name'])) {
    $data['promo'] = [
        'name' => $data['promo_name'],
        'discount_type' => $data['promo_type'],
        'discount_value' => (float)$data['promo_value']
    ];
} else {
    $data['promo'] = null;
}

$presSql = "
    SELECT 
        drug, 
        frequency, 
        dosage, 
        duration, 
        quantity, 
        instructions
    FROM dental_prescription
    WHERE appointment_transaction_id = ?
";
$stmtPres = $conn->prepare($presSql);
$stmtPres->bind_param("i", $data['appointment_transaction_id']);
$stmtPres->execute();
$resPres = $stmtPres->get_result();

$prescriptions = [];
while ($row = $resPres->fetch_assoc()) {
    $prescriptions[] = $row;
}
$data['prescriptions'] = $prescriptions;

$data['xray_results'] = [];
if (!empty($data['xray_file'])) {
    $data['xray_results'][] = $data['xray_file'];
}

echo json_encode($data, JSON_PRETTY_PRINT);
$conn->close();
?>
