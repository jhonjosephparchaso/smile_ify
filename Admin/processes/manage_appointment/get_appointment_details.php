<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$appointmentId = $_GET['id'] ?? null;
if (!$appointmentId) {
    http_response_code(400);
    echo json_encode(['error' => 'No appointment ID provided.']);
    exit();
}

$sql = "
    SELECT 
        u.user_id,
        u.guardian_id,
        u.first_name, 
        u.middle_name, 
        u.last_name, 
        u.gender, 
        u.date_of_birth,
        u.date_of_birth_iv,
        u.date_of_birth_tag,
        u.email, 
        u.contact_number,
        u.contact_number_iv,
        u.contact_number_tag,
        u.address,
        u.address_iv,
        u.address_tag,
        u.date_created AS user_created,
        u.date_updated,

        a.appointment_transaction_id,
        a.branch_id,
        a.dentist_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.notes,
        a.date_created,

        b.name AS branch_name,

        GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR '\n') AS services,
        GROUP_CONCAT(DISTINCT aps.service_id) AS service_ids_raw,

        d.first_name AS dentist_first,
        d.last_name AS dentist_last
    FROM appointment_transaction a
    INNER JOIN users u ON a.user_id = u.user_id
    INNER JOIN branch b ON a.branch_id = b.branch_id
    LEFT JOIN appointment_services aps ON a.appointment_transaction_id = aps.appointment_transaction_id
    LEFT JOIN service s ON aps.service_id = s.service_id
    LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
    WHERE a.appointment_transaction_id = ?
    GROUP BY a.appointment_transaction_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $checkTx = $conn->prepare("SELECT COUNT(*) AS c FROM dental_transaction WHERE appointment_transaction_id = ?");
    $checkTx->bind_param("i", $appointmentId);
    $checkTx->execute();
    $hasTransaction = $checkTx->get_result()->fetch_assoc()['c'];

    $checkVit = $conn->prepare("SELECT COUNT(*) AS c FROM dental_vital WHERE appointment_transaction_id = ?");
    $checkVit->bind_param("i", $appointmentId);
    $checkVit->execute();
    $hasVitals = $checkVit->get_result()->fetch_assoc()['c'];

    $checkPres = $conn->prepare("SELECT COUNT(*) AS c FROM dental_prescription WHERE appointment_transaction_id = ?");
    $checkPres->bind_param("i", $appointmentId);
    $checkPres->execute();
    $hasPrescriptions = $checkPres->get_result()->fetch_assoc()['c'];

    $decryptedDOB = null;
    if (!empty($row['date_of_birth']) && !empty($row['date_of_birth_iv']) && !empty($row['date_of_birth_tag'])) {
        $decryptedDOB = decryptField(
            $row['date_of_birth'],
            $row['date_of_birth_iv'],
            $row['date_of_birth_tag']
        );
    }

    $decryptedContact = null;
    if (!empty($row['contact_number']) && !empty($row['contact_number_iv']) && !empty($row['contact_number_tag'])) {
        $decryptedContact = decryptField(
            $row['contact_number'],
            $row['contact_number_iv'],
            $row['contact_number_tag']
        );
    }

    $decryptedAddress = null;
    if (!empty($row['address']) && !empty($row['address_iv']) && !empty($row['address_tag'])) {
        $decryptedAddress = decryptField(
            $row['address'],
            $row['address_iv'],
            $row['address_tag']
        );
    }

    $isDependent = !empty($row['guardian_id']);
    $guardianInfo = null;

    if ($isDependent) {
        $g = $conn->prepare("
            SELECT 
                first_name, middle_name, last_name, gender,
                date_of_birth, date_of_birth_iv, date_of_birth_tag,
                email, 
                contact_number, contact_number_iv, contact_number_tag,
                address, address_iv, address_tag
            FROM users 
            WHERE user_id = ?
        ");
        $g->bind_param("i", $row['guardian_id']);
        $g->execute();
        $gRes = $g->get_result();

        if ($gRes && $gRow = $gRes->fetch_assoc()) {

            $gDOB = decryptField($gRow['date_of_birth'], $gRow['date_of_birth_iv'], $gRow['date_of_birth_tag']);
            $gContact = decryptField($gRow['contact_number'], $gRow['contact_number_iv'], $gRow['contact_number_tag']);
            $gAddress = decryptField($gRow['address'], $gRow['address_iv'], $gRow['address_tag']);

            $guardianInfo = [
                "full_name" => trim(($gRow['first_name'] ?? '') . ' ' . ($gRow['middle_name'] ?? '') . ' ' . ($gRow['last_name'] ?? '')),
                "gender" => ucfirst($gRow['gender']),
                "dob" => $gDOB ? date("F j, Y", strtotime($gDOB)) : "-",
                "email" => $gRow['email'] ?? "-",
                "contact_number" => $gContact ?? "-",
                "address" => $gAddress ?? "-"
            ];
        }
    }

    $serviceIds = [];
    if (!empty($row['service_ids_raw'])) {
        $serviceIds = array_map('intval', explode(',', $row['service_ids_raw']));
    }

    $profile = [
        'full_name' => trim($row['first_name'] . ' ' . ($row['middle_name'] ?? '') . ' ' . $row['last_name']),
        'gender' => ucfirst($row['gender']),
        'date_of_birth' => $decryptedDOB ? date("F j, Y", strtotime($decryptedDOB)) : '-',
        'email' => $row['email'],
        'contact_number' => $decryptedContact ?? '-',
        'address' => $decryptedAddress ?? '-',
        'joined' => $row['user_created'] ? date("F j, Y", strtotime($row['user_created'])) : '-',
        'date_updated' => $row['date_updated'] ? date("F j, Y", strtotime($row['date_updated'])) : '-',

        'appointment_transaction_id' => $row['appointment_transaction_id'],
        'appointment_date' => $row['appointment_date'] ? date("F j, Y", strtotime($row['appointment_date'])) : '-',
        'appointment_time' => $row['appointment_time'] ? date("g:i A", strtotime($row['appointment_time'])) : '-',
        'status' => $row['status'],
        'notes' => $row['notes'] ?? '-',

        'branch' => $row['branch_name'],
        'services' => $row['services'] ?: '-',
        'dentist' => $row['dentist_first']
            ? 'Dr. ' . trim($row['dentist_first'] . ' ' . $row['dentist_last'])
            : 'Not Assigned',
        'date_created' => $row['date_created'] ? date("F j, Y", strtotime($row['date_created'])) : '-',

        'raw_appointment_date' => $row['appointment_date'],
        'raw_appointment_time' => $row['appointment_time'],
        'branch_id' => intval($row['branch_id']),
        'dentist_id' => intval($row['dentist_id']),
        'services_ids' => $serviceIds,

        'has_transaction' => $hasTransaction,
        'has_vitals' => $hasVitals,
        'has_prescriptions' => $hasPrescriptions,
        
        'guardian_info' => $guardianInfo,
        'is_dependent' => $isDependent,
    ];

    header('Content-Type: application/json');
    echo json_encode($profile);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Appointment not found or no user linked.']);
}

$conn->close();
?>
