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
        a.appointment_transaction_id,
        b.name AS branch,
        GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS services,
        CONCAT('Dr. ', d.first_name, ' ', IF(d.middle_name IS NOT NULL AND d.middle_name != '', CONCAT(LEFT(d.middle_name, 1), '. '), ''), d.last_name) AS dentist,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.date_created
    FROM appointment_transaction a
    LEFT JOIN branch b ON a.branch_id = b.branch_id
    LEFT JOIN appointment_services aps ON a.appointment_transaction_id = aps.appointment_transaction_id
    LEFT JOIN service s ON aps.service_id = s.service_id
    LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
    WHERE a.user_id = ?
    GROUP BY a.appointment_transaction_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patientID);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        $row['appointment_transaction_id'] ?: '-',
        $row['dentist'] ?: 'Available Dentist',
        $row['services'] ?: '-',
        $row['appointment_date'],
        $row['appointment_time'] ? date("g:i A", strtotime($row['appointment_time'])) : '-',
        $row['status'],
        '<button class="btn-action" data-type="appointment" data-id="' . $row['appointment_transaction_id'] . '">View</button>',
        $row['date_created'] ? date("F j, Y", strtotime($row['date_created'])) : '-'
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $appointments]);
$conn->close();
?>
