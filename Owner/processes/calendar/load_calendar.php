<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

function stringToColorCode($str) {
    $code = dechex(crc32($str));
    $code = str_pad($code, 6, '0', STR_PAD_LEFT);
    return '#' . substr($code, 0, 6);
}

$sql = "
    SELECT 
        a.appointment_transaction_id,
        CONCAT(u.first_name, ' ', u.last_name) AS patient,
        b.nickname AS branch,
        GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ', ') AS services,
        CONCAT(d.first_name, ' ', d.last_name) AS dentist,
        a.appointment_date,
        a.appointment_time,
        a.notes,
        a.date_created,
        a.status
    FROM appointment_transaction a
    LEFT JOIN branch b ON a.branch_id = b.branch_id
    LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
    LEFT JOIN users u ON a.user_id = u.user_id
    LEFT JOIN appointment_services aps ON a.appointment_transaction_id = aps.appointment_transaction_id
    LEFT JOIN service s ON aps.service_id = s.service_id
    GROUP BY a.appointment_transaction_id
    ORDER BY a.appointment_date, a.appointment_time
";

$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {

    $statusColor = '#fe9705';
    if ($row['status'] === 'Completed') {
        $statusColor = '#3ac430';
    } elseif ($row['status'] === 'Cancelled') {
        $statusColor = '#d11313';
    } elseif ($row['status'] === 'Pending Reschedule') {
        $statusColor = '#0066ff';
    }

    $branchColor = stringToColorCode($row['branch']);
    $serviceList = $row['services'] ?? '-';

    $events[] = [
        'id' => $row['appointment_transaction_id'],
        'patient' => $row['patient'],
        'title' => $serviceList,
        'start' => $row['appointment_date'] . 'T' . $row['appointment_time'],
        'branch' => $row['branch'],
        'services' => $serviceList,
        'dentist' => $row['dentist'],
        'notes' => $row['notes'],
        'status' => $row['status'],
        'date_created' => $row['date_created'],
        'color' => $statusColor,
        'branchColor' => $branchColor
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
$conn->close();
?>
