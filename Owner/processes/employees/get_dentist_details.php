<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No dentist ID provided"]);
    exit();
}

$dentistId = intval($_GET['id']);

$sql = "SELECT 
            dentist_id,
            last_name,
            middle_name,
            first_name,
            gender,
            date_of_birth,
            date_of_birth_iv,
            date_of_birth_tag,
            email,
            contact_number,
            contact_number_iv,
            contact_number_tag,
            license_number,
            license_number_iv,
            license_number_tag,
            status,
            signature_image,
            profile_image,
            date_started,
            date_updated,
            date_created
        FROM dentist
        WHERE dentist_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dentistId);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo json_encode(["error" => "Dentist not found"]);
    exit();
}

if ($row['date_of_birth']) {
    $row['date_of_birth'] = decryptField(
        $row['date_of_birth'],
        $row['date_of_birth_iv'],
        $row['date_of_birth_tag'],
        $ENCRYPTION_KEY
    );
}

if ($row['contact_number']) {
    $row['contact_number'] = decryptField(
        $row['contact_number'],
        $row['contact_number_iv'],
        $row['contact_number_tag'],
        $ENCRYPTION_KEY
    );
}

if ($row['license_number']) {
    $row['license_number'] = decryptField(
        $row['license_number'],
        $row['license_number_iv'],
        $row['license_number_tag'],
        $ENCRYPTION_KEY
    );
}

unset(
    $row['date_of_birth_iv'], $row['date_of_birth_tag'],
    $row['contact_number_iv'], $row['contact_number_tag'],
    $row['license_number_iv'], $row['license_number_tag']
);

$branchSql = "SELECT branch_id FROM dentist_branch WHERE dentist_id = ?";
$stmt2 = $conn->prepare($branchSql);
$stmt2->bind_param("i", $dentistId);
$stmt2->execute();
$resBranches = $stmt2->get_result();

$branches = [];
while ($b = $resBranches->fetch_assoc()) {
    $branches[] = (int)$b['branch_id'];
}
$row['branches'] = $branches;
$stmt2->close();

$serviceSql = "SELECT service_id FROM dentist_service WHERE dentist_id = ?";
$stmt3 = $conn->prepare($serviceSql);
$stmt3->bind_param("i", $dentistId);
$stmt3->execute();
$resServices = $stmt3->get_result();

$services = [];
while ($s = $resServices->fetch_assoc()) {
    $services[] = (int)$s['service_id'];
}
$row['services'] = $services;
$stmt3->close();

$schedSql = "
    SELECT 
        schedule_id,
        dentist_id,
        day,
        branch_id,
        start_time,
        end_time
    FROM dentist_schedule
    WHERE dentist_id = ?
    ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
";

$stmt4 = $conn->prepare($schedSql);
$stmt4->bind_param("i", $dentistId);
$stmt4->execute();
$resSched = $stmt4->get_result();

$schedule = [];

while ($sc = $resSched->fetch_assoc()) {
    $day = $sc['day'];

    if (!isset($schedule[$day])) {
        $schedule[$day] = [];
    }

    $schedule[$day][] = [
        "schedule_id"  => (int)$sc['schedule_id'],
        "dentist_id"   => (int)$sc['dentist_id'],
        "day"          => $sc['day'],
        "branch_id"    => (int)$sc['branch_id'],
        "start_time"   => $sc['start_time'] ? date("H:i", strtotime($sc['start_time'])) : "",
        "end_time"     => $sc['end_time'] ? date("H:i", strtotime($sc['end_time'])) : ""
    ];
}

$row['branch_schedule'] = $schedule;

$stmt4->close();

echo json_encode($row);
$conn->close();
?>
