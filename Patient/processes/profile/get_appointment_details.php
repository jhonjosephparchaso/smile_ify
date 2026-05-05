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
    echo json_encode(["error" => "No appointment ID provided"]);
    exit();
}

$appointmentId = intval($_GET['id']);
$loggedInUser = $_SESSION['user_id'];

$sql = "
    SELECT 
        a.user_id,
        b.name AS branch,
        GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR '\n') AS services,
        CONCAT('Dr. ', d.first_name, ' ', IF(d.middle_name IS NOT NULL AND d.middle_name != '', CONCAT(LEFT(d.middle_name, 1), '. '), ''), d.last_name) AS dentist,
        a.appointment_date,
        a.appointment_time,
        a.notes,
        a.date_created,
        a.status
    FROM appointment_transaction a
    LEFT JOIN branch b ON a.branch_id = b.branch_id
    LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
    LEFT JOIN appointment_services aps ON a.appointment_transaction_id = aps.appointment_transaction_id
    LEFT JOIN service s ON aps.service_id = s.service_id
    WHERE a.appointment_transaction_id = ?
    GROUP BY a.appointment_transaction_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();

if (!($row = $result->fetch_assoc())) {
    echo json_encode(["error" => "Appointment not found"]);
    exit();
}

$appointmentUser = intval($row['user_id']);

$guardianCheck = $conn->prepare("SELECT guardian_id FROM users WHERE user_id = ?");
$guardianCheck->bind_param("i", $appointmentUser);
$guardianCheck->execute();
$dependentData = $guardianCheck->get_result()->fetch_assoc();

$isGuardian = $dependentData && intval($dependentData['guardian_id']) === $loggedInUser;

if ($appointmentUser !== $loggedInUser && !$isGuardian) {
    echo json_encode(["error" => "Appointment not found"]);
    exit();
}

$row['services'] = $row['services'] ?: '-';

echo json_encode($row);
$conn->close();
?>
