<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No transaction ID provided"]);
    exit();
}

$transactionId = intval($_GET['id']);
$loggedInUser  = $_SESSION['user_id'];

$sql = "
    SELECT 
        a.user_id AS appointment_user_id,
        u.guardian_id,
        b.name AS branch,
        GROUP_CONCAT(DISTINCT CONCAT(s.name, ' × ', dts.quantity) ORDER BY s.name SEPARATOR '\n') AS services,
        CONCAT('Dr. ', d.first_name, ' ', IF(d.middle_name IS NOT NULL AND d.middle_name != '', CONCAT(LEFT(d.middle_name, 1), '. '), ''), d.last_name) AS dentist,
        d.last_name AS dentist_last_name,
        d.first_name AS dentist_first_name,
        d.middle_name AS dentist_middle_name,
        d.license_number,
        d.license_number_iv,
        d.license_number_tag,
        d.signature_image,
        u.last_name AS patient_last_name,
        u.first_name AS patient_first_name,
        u.middle_name AS patient_middle_name,
        u.date_of_birth AS patient_dob,
        u.date_of_birth_iv AS patient_dob_iv,
        u.date_of_birth_tag AS patient_dob_tag,
        u.gender AS gender,
        a.appointment_transaction_id,
        a.appointment_date,
        a.appointment_time,
        dt.dental_transaction_id,
        dt.notes,
        dt.total,
        dt.additional_payment,
        dt.payment_method,
        dt.xray_file,
        dt.date_created,
        dt.prescription_downloaded,
        dt.medcert_status,
        dt.diagnosis,
        dt.fitness_status,
        dt.remarks,
        dt.medcert_notes,
        dt.medcert_requested_date,
        dv.body_temp,
        dv.pulse_rate,
        dv.respiratory_rate,
        dv.blood_pressure,
        dv.height,
        dv.weight,
        dv.is_swelling,
        dv.is_sensitive,
        dv.is_bleeding
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
    LEFT JOIN dental_vital dv
        ON dv.appointment_transaction_id = a.appointment_transaction_id
    LEFT JOIN users u 
        ON a.user_id = u.user_id
    WHERE dt.dental_transaction_id = ?
    GROUP BY dt.dental_transaction_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transactionId);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo json_encode(["error" => "Transaction not found"]);
    exit();
}

$ownerId    = (int) $row['appointment_user_id'];
$guardianId = isset($row['guardian_id']) ? (int) $row['guardian_id'] : 0;

if ($ownerId !== $loggedInUser && $guardianId !== $loggedInUser) {
    echo json_encode(["error" => "Transaction not found"]);
    exit();
}

$row['services'] = $row['services'] ?: '-';

if (!empty($row['license_number']) && !empty($row['license_number_iv']) && !empty($row['license_number_tag'])) {
    $row['license_number'] = decryptField(
        $row['license_number'],
        $row['license_number_iv'],
        $row['license_number_tag'],
        $ENCRYPTION_KEY
    );
}

if (!empty($row['patient_dob']) && !empty($row['patient_dob_iv']) && !empty($row['patient_dob_tag'])) {
    $row['patient_dob'] = decryptField(
        $row['patient_dob'],
        $row['patient_dob_iv'],
        $row['patient_dob_tag'],
        $ENCRYPTION_KEY
    );
}

$appointmentTransactionId = $row['appointment_transaction_id'];

$prescriptionsSql = "
    SELECT drug, frequency, dosage, duration, quantity, instructions 
    FROM dental_prescription 
    WHERE appointment_transaction_id = ?
";
$stmt2 = $conn->prepare($prescriptionsSql);
$stmt2->bind_param("i", $appointmentTransactionId);
$stmt2->execute();
$prescriptionsResult = $stmt2->get_result();

$prescriptions = [];
while ($p = $prescriptionsResult->fetch_assoc()) {
    $prescriptions[] = $p;
}

$row['prescriptions'] = $prescriptions;

if (!empty($row['xray_file'])) {
    $row['xray_results'] = [
        [
            "file_path"    => $row['xray_file'],
            "service_name" => null,
            "date_created" => $row["date_created"]
        ]
    ];
} else {
    $row['xray_results'] = [];
}

echo json_encode($row);
$conn->close();
exit;
