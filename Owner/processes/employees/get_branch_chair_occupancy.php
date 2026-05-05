<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$branch_id = intval($_GET['branch_id'] ?? 0);
if (!$branch_id) {
    echo json_encode(["error" => "Missing branch_id"]);
    exit;
}

$b = $conn->prepare("SELECT dental_chairs FROM branch WHERE branch_id = ?");
$b->bind_param("i", $branch_id);
$b->execute();
$chairs = (int)($b->get_result()->fetch_assoc()['dental_chairs'] ?? 0);
$b->close();

$sql = "
    SELECT 
        ds.day,
        ds.start_time,
        ds.end_time,
        CONCAT(d.first_name, ' ', d.last_name) AS dentist_name
    FROM dentist_schedule ds
    JOIN dentist d ON d.dentist_id = ds.dentist_id
    WHERE ds.branch_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$res = $stmt->get_result();

$occupied = [];

while ($row = $res->fetch_assoc()) {
    $day = $row['day'];

    if (!isset($occupied[$day])) {
        $occupied[$day] = [];
    }

    $start = strtotime($row['start_time']);
    $end   = strtotime($row['end_time']);

    for ($t = $start; $t < $end; $t += 1800) {
        $slot = date("H:i", $t);

        if (!isset($occupied[$day][$slot])) {
            $occupied[$day][$slot] = [
                "used" => 0,
                "dentists" => []
            ];
        }

        $occupied[$day][$slot]["used"]++;
        $occupied[$day][$slot]["dentists"][] = $row['dentist_name'];
    }
}

$stmt->close();

echo json_encode([
    "dental_chairs" => $chairs,
    "occupied" => $occupied
]);

$conn->close();
