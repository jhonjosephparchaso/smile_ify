<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$appointmentId = (int)($_GET['appointment_id'] ?? 0);

if ($appointmentId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid appointment reference."
    ]);
    exit;
}

$appt = $conn->query("
    SELECT 
        at.appointment_transaction_id,
        at.branch_id,
        b.name AS branch_name,
        at.appointment_date,
        at.appointment_time,
        CONCAT(u.first_name,' ',u.last_name) AS patient_name,
        CONCAT(d.first_name,' ',d.last_name) AS original_dentist
    FROM appointment_transaction at
    JOIN branch b ON b.branch_id = at.branch_id
    JOIN users u ON u.user_id = at.user_id
    JOIN dentist d ON d.dentist_id = at.dentist_id
    WHERE at.appointment_transaction_id = $appointmentId
    AND at.status = 'Pending Reschedule'
    AND (
        at.user_id = $userId
        OR u.guardian_id = $userId
    )
    LIMIT 1
")->fetch_assoc();

if (!$appt) {
    echo json_encode([
        "success" => false,
        "message" => "No pending appointment found."
    ]);
    exit;
}

$originalBranch = (int)$appt['branch_id'];

$serviceIds = [];
$resSvc = $conn->query("
    SELECT service_id
    FROM appointment_services
    WHERE appointment_transaction_id = $appointmentId
");
while ($r = $resSvc->fetch_assoc()) {
    $serviceIds[] = (int)$r['service_id'];
}
if (empty($serviceIds)) {
    echo json_encode(["success" => false, "message" => "Appointment has no services assigned."]);
    exit;
}
$serviceIdList = implode(',', $serviceIds);

$timeSlots = [
    '09:00:00','09:30:00','10:00:00','10:30:00','11:00:00','11:30:00',
    '13:00:00','13:30:00','14:00:00','14:30:00','15:00:00','15:30:00'
];

$datesToCheck = [];
$start = new DateTime($appt['appointment_date']);
$start->modify('+1 day'); 

for ($i = 0; $i < 14; $i++) {
    $d = clone $start;
    $d->modify("+$i day");
    $datesToCheck[] = $d;
}

foreach ($datesToCheck as $date) {
    $dateStr = $date->format('Y-m-d');

    $triedBranches = [];

    $branches = $conn->query("
        SELECT branch_id, name
        FROM branch
        WHERE status = 'Active'
        ORDER BY name ASC
    ");

    while ($b = $branches->fetch_assoc()) {

        $dayName = $date->format('l');

        $closedRes = $conn->query("
            SELECT 1
            FROM branch_announcements ba
            JOIN announcements a ON a.announcement_id = ba.announcement_id
            WHERE a.type = 'Closed'
                AND ba.status = 'Active'
                AND ba.branch_id = {$b['branch_id']}
                AND ba.start_date <= '$dateStr'
                AND ba.end_date >= '$dateStr'
            LIMIT 1
        ");
        if ($closedRes && $closedRes->num_rows > 0) {
            $triedBranches[] = $b['branch_id'];
            continue;
        }

        $svcCheck = $conn->query("
            SELECT COUNT(DISTINCT service_id) AS cnt
            FROM branch_service
            WHERE branch_id = {$b['branch_id']}
                AND status = 'Active'
                AND service_id IN ($serviceIdList)
        ")->fetch_assoc();

        if ((int)$svcCheck['cnt'] !== count($serviceIds)) {
            continue;
        }

        $dentists = $conn->query("
            SELECT 
                d.dentist_id,
                CONCAT(d.first_name,' ',d.last_name) AS name,
                ds.start_time,
                ds.end_time
            FROM dentist d
            JOIN dentist_branch db ON db.dentist_id = d.dentist_id
            JOIN dentist_schedule ds
                ON ds.dentist_id = d.dentist_id
                AND ds.branch_id = db.branch_id
            WHERE d.status = 'Active'
            AND db.branch_id = {$b['branch_id']}
            AND ds.day = '$dayName'
        ");

        while ($d = $dentists->fetch_assoc()) {

            $chk = $conn->query("
                SELECT COUNT(DISTINCT service_id) AS cnt
                FROM dentist_service
                WHERE dentist_id = {$d['dentist_id']}
                    AND service_id IN ($serviceIdList)
            ")->fetch_assoc();
            if ((int)$chk['cnt'] !== count($serviceIds)) continue;

            $resDur = $conn->query("
                SELECT SUM(s.duration_minutes * aps.quantity) AS total_minutes
                FROM appointment_services aps
                JOIN service s ON s.service_id = aps.service_id
                WHERE aps.appointment_transaction_id = $appointmentId
            ")->fetch_assoc();

            $totalMinutes = (int)$resDur['total_minutes'];

            if ($totalMinutes <= 0) {
                echo json_encode(["success" => false, "message" => "Invalid service duration."]);
                exit;
            }

            $booked = [];

            $res = $conn->query("
                SELECT 
                    at.appointment_time,
                    SUM(s.duration_minutes * aps.quantity) AS total_minutes
                FROM appointment_transaction at
                JOIN appointment_services aps 
                    ON aps.appointment_transaction_id = at.appointment_transaction_id
                JOIN service s 
                    ON s.service_id = aps.service_id
                WHERE at.branch_id = {$b['branch_id']}
                    AND at.dentist_id = {$d['dentist_id']}
                    AND at.appointment_date = '$dateStr'
                    AND at.status = 'Booked'
                GROUP BY at.appointment_transaction_id
            ");

            while ($r = $res->fetch_assoc()) {
                $start = strtotime($r['appointment_time']);
                $end   = strtotime("+{$r['total_minutes']} minutes", $start);

                $booked[] = [
                    'start' => $start,
                    'end'   => $end
                ];
            }

            foreach ($timeSlots as $slot) {

                $slotStart = strtotime($slot);
                $slotEnd   = strtotime("+$totalMinutes minutes", $slotStart);

                if ($slotStart < strtotime($d['start_time']) || $slotEnd > strtotime($d['end_time'])) {
                    continue;
                }

                $overlap = false;
                foreach ($booked as $bk) {
                    if ($slotStart < $bk['end'] && $slotEnd > $bk['start']) {
                        $overlap = true;
                        break;
                    }
                }

                if ($overlap) continue;

                echo json_encode([
                    "success" => true,
                    "appointment_id" => $appointmentId,
                    "patient" => $appt['patient_name'],
                    "original_branch" => $appt['branch_name'],
                    "original_date" => date('F d, Y', strtotime($appt['appointment_date'])),
                    "original_time" => date('h:i A', strtotime($appt['appointment_time'])),
                    "original_dentist" => $appt['original_dentist'],
                    "branch_id" => $b['branch_id'],
                    "branch" => $b['name'],
                    "dentist_id" => $d['dentist_id'],
                    "dentist" => $d['name'],
                    "date" => date('F d, Y', strtotime($dateStr)),
                    "time" => date('h:i A', strtotime($slot)),
                    "date_raw" => $dateStr,
                    "time_raw" => $slot
                ]);
                exit;
            }
        }
    }
}

echo json_encode([
    "success" => false,
    "message" => "No available slots found in the next 14 days."
]);
