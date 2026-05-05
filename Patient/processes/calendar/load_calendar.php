<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

function stringToColorCode($str) {
    $code = dechex(crc32($str));
    $code = str_pad($code, 6, '0', STR_PAD_LEFT);
    return "#" . substr($code, 0, 6);
}

$guardian_id = $_SESSION['user_id'];

$ids = [$guardian_id];
$dependentNames = [];

$dep_stmt = $conn->prepare("SELECT user_id, first_name, last_name FROM users WHERE guardian_id = ?");
$dep_stmt->bind_param("i", $guardian_id);
$dep_stmt->execute();
$dep_result = $dep_stmt->get_result();

while ($dep = $dep_result->fetch_assoc()) {
    $ids[] = $dep['user_id'];
    $dependentNames[$dep['user_id']] = $dep['first_name'] . " " . $dep['last_name'];
}

$guardianName_query = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
$guardianName_query->bind_param("i", $guardian_id);
$guardianName_query->execute();
$guardianData = $guardianName_query->get_result()->fetch_assoc();
$dependentNames[$guardian_id] = $guardianData['first_name'] . " " . $guardianData['last_name'];

$placeholders = implode(",", array_fill(0, count($ids), "?"));
$types = str_repeat("i", count($ids));

$sql = "
    SELECT 
        a.appointment_transaction_id,
        CONCAT(u.first_name, ' ', u.last_name) AS patient,
        a.user_id,
        b.name AS branch,
        GROUP_CONCAT(s.name SEPARATOR '\n') AS services,
        CONCAT(d.first_name, ' ', d.last_name) AS dentist,
        a.appointment_date,
        a.appointment_time,
        a.notes,
        a.date_created,
        a.status
    FROM appointment_transaction a
    LEFT JOIN branch b ON a.branch_id = b.branch_id
    LEFT JOIN appointment_services aps ON a.appointment_transaction_id = aps.appointment_transaction_id
    LEFT JOIN users u ON a.user_id = u.user_id
    LEFT JOIN service s ON aps.service_id = s.service_id
    LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
    WHERE a.user_id IN ($placeholders)
    GROUP BY a.appointment_transaction_id
    ORDER BY a.appointment_date, a.appointment_time
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

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
    $patientName = $dependentNames[$row['user_id']] ?? "Unknown";

    $events[] = [
        'id' => $row['appointment_transaction_id'],
        'title' => $patientName . ": " . $serviceList,
        'start' => $row['appointment_date'] . 'T' . $row['appointment_time'],
        'branch' => $row['branch'],
        'services' => $serviceList,
        'dentist' => $row['dentist'],
        'patient' => $patientName,
        'notes' => $row['notes'],
        'status' => $row['status'],
        'date_created' => $row['date_created'],
        'color' => $statusColor,
        'branchColor' => $branchColor
    ];
}

header("Content-Type: application/json");
echo json_encode($events);
$conn->close();
?>
