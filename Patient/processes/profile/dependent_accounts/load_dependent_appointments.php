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
        a.appointment_transaction_id,
        b.name AS branch,
        GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ', ') AS services,
        CONCAT('Dr. ', d.last_name, ', ', d.first_name, ' ', IFNULL(d.middle_name, '')) AS dentist,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.date_created
    FROM appointment_transaction a
    LEFT JOIN branch b ON a.branch_id = b.branch_id
    LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
    LEFT JOIN appointment_services aps ON a.appointment_transaction_id = aps.appointment_transaction_id
    LEFT JOIN service s ON aps.service_id = s.service_id
    WHERE a.user_id = ?
    GROUP BY a.appointment_transaction_id
    ORDER BY a.date_created DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dependent_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        $row['dentist'] ?: 'Available Dentist',
        $row['branch'] ?: '-',
        $row['services'] ?: '-',
        $row['appointment_date'],
        substr($row['appointment_time'], 0, 5),
        $row['status'],
        '<button class="btn-action" data-type="appointment" data-id="'.$row['appointment_transaction_id'].'">View</button>',
        $row['date_created']
    ];
}

echo json_encode(["data" => $appointments]);
$conn->close();
?>
