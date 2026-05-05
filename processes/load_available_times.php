<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

$branchId        = isset($_POST['branch_id']) ? intval($_POST['branch_id']) : null;
$appointmentDate = $_POST['appointment_date'] ?? null;

$requestedUserId = isset($_POST['user_id']) ? intval($_POST['user_id']) : (isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null);

if (!$branchId || !$appointmentDate) {
    echo json_encode(['error' => 'Missing required inputs.']);
    exit;
}

$startTime = new DateTime('09:00');
$endTime   = new DateTime('16:30');
$stepMinutes = 30;
$cleanupBuffer = 15;

$sql = "
    SELECT d.dentist_id
    FROM dentist d
    INNER JOIN dentist_branch db ON d.dentist_id = db.dentist_id
    WHERE db.branch_id = ? AND d.status = 'Active'
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branchId);
$stmt->execute();
$res = $stmt->get_result();

$dentists = [];
while ($r = $res->fetch_assoc()) {
    $dentists[] = intval($r['dentist_id']);
}
$stmt->close();

if (empty($dentists)) {
    echo json_encode([
        'times' => [],
        'blocked' => []
    ]);
    exit;
}

$dayName = date('l', strtotime($appointmentDate));
$placeholders = implode(',', array_fill(0, count($dentists), '?'));
$types = str_repeat('i', count($dentists));

$sql = "
    SELECT dentist_id, start_time, end_time
    FROM dentist_schedule
    WHERE branch_id = ?
        AND day = ?
        AND dentist_id IN ($placeholders)
";
$stmt = $conn->prepare($sql);

$bindParams = [];
$bindParams[] = $branchId;
$bindParams[] = $dayName;
$refArr = [];
$types_full = 'is' . $types;
$refArr[] = & $types_full;
$refArr[] = & $bindParams[0];
$refArr[] = & $bindParams[1];

for ($i = 0; $i < count($dentists); $i++) {
    $bindParams[] = $dentists[$i];
    $refArr[] = & $bindParams[2 + $i];
}
call_user_func_array(array($stmt, 'bind_param'), $refArr);
$stmt->execute();
$res = $stmt->get_result();

$dentistSchedules = [];
while ($row = $res->fetch_assoc()) {
    $did = intval($row['dentist_id']);
    if (is_null($row['start_time']) || is_null($row['end_time']) || $row['start_time'] === '' || $row['end_time'] === '') {
        $dentistSchedules[$did][] = ['start' => '09:00', 'end' => '16:30'];
    } else {
        $s = date('H:i', strtotime($row['start_time']));
        $e = date('H:i', strtotime($row['end_time']));
        $dentistSchedules[$did][] = ['start' => $s, 'end' => $e];
    }
}
$stmt->close();

$sql = "
    SELECT at.appointment_time, at.dentist_id, at.user_id,
        SUM(s.duration_minutes) AS total_duration
    FROM appointment_transaction AS at
    INNER JOIN appointment_services AS ats ON at.appointment_transaction_id = ats.appointment_transaction_id
    INNER JOIN service AS s ON ats.service_id = s.service_id
    WHERE at.branch_id = ?
        AND at.appointment_date = ?
        AND at.status IN ('Booked','Approved','Confirmed')
    GROUP BY at.appointment_transaction_id, at.appointment_time, at.dentist_id, at.user_id
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $branchId, $appointmentDate);
$stmt->execute();
$res = $stmt->get_result();

$bookedByDentist = [];
$unassignedBookings = [];
$userBookedSlotsExact = [];

while ($row = $res->fetch_assoc()) {
    $dentist_id_db = $row['dentist_id'];
    $isDentistNull = is_null($dentist_id_db) || $dentist_id_db === '';

    $start = DateTime::createFromFormat('H:i:s', $row['appointment_time'])
        ?: DateTime::createFromFormat('H:i', $row['appointment_time']);
    if (!$start) continue;

    $duration = intval($row['total_duration']);
    $end = (clone $start)->modify("+".($duration + $cleanupBuffer)." minutes");

    $rowUserId = intval($row['user_id']);

    if ($isDentistNull) {
        $unassignedBookings[] = ['start' => $start, 'end' => $end, 'user_id' => $rowUserId];
    } else {
        $did = intval($dentist_id_db);
        $bookedByDentist[$did][] = ['start' => $start, 'end' => $end, 'user_id' => $rowUserId];
    }

    if ($requestedUserId && $rowUserId === $requestedUserId) {
        $userBookedSlotsExact[$start->format('H:i')] = true;
    }
}
$stmt->close();

$userBookings = [];

if ($requestedUserId) {
    $sql = "
        SELECT at.appointment_time, SUM(s.duration_minutes) AS total_duration
        FROM appointment_transaction AS at
        INNER JOIN appointment_services AS ats ON at.appointment_transaction_id = ats.appointment_transaction_id
        INNER JOIN service AS s ON ats.service_id = s.service_id
        WHERE at.user_id = ?
            AND at.appointment_date = ?
            AND at.status IN ('Booked','Approved','Confirmed')
        GROUP BY at.appointment_transaction_id, at.appointment_time
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $requestedUserId, $appointmentDate);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $start = DateTime::createFromFormat('H:i:s', $row['appointment_time'])
            ?: DateTime::createFromFormat('H:i', $row['appointment_time']);
        if (!$start) continue;
        $duration = intval($row['total_duration']);
        $end = (clone $start)->modify("+".($duration + $cleanupBuffer)." minutes");
        $userBookings[] = ['start' => $start, 'end' => $end];
    }
    $stmt->close();
}

function slot_overlaps(DateTime $slot, DateTime $s, DateTime $e) {
    return ($slot >= $s && $slot < $e);
}

$available = [];
$blocked = [];

$slot = clone $startTime;
$interval = new DateInterval('PT'.$stepMinutes.'M');

while ($slot < $endTime) {
    $slotStr = $slot->format('H:i');

    $scheduledDentistCount = 0;
    $dentistsScheduledNow = [];
    foreach ($dentists as $did) {
        if (!isset($dentistSchedules[$did])) continue;
        $isScheduled = false;
        foreach ($dentistSchedules[$did] as $seg) {
            $segStart = DateTime::createFromFormat('H:i', $seg['start']);
            $segEnd   = DateTime::createFromFormat('H:i', $seg['end']);
            if ($segStart && $segEnd && $slot >= $segStart && $slot < $segEnd) {
                $isScheduled = true;
                break;
            }
        }
        if ($isScheduled) {
            $scheduledDentistCount++;
            $dentistsScheduledNow[] = $did;
        }
    }

    if ($scheduledDentistCount === 0) {
        $blocked[] = $slotStr;
        $slot->add($interval);
        continue;
    }

    $occupiedDentists = 0;
    foreach ($dentistsScheduledNow as $did) {
        $hasConflict = false;
        if (isset($bookedByDentist[$did])) {
            foreach ($bookedByDentist[$did] as $appt) {
                if (slot_overlaps($slot, $appt['start'], $appt['end'])) {
                    $hasConflict = true;
                    break;
                }
            }
        }
        if ($hasConflict) $occupiedDentists++;
    }

    $unassignedConflicts = 0;
    foreach ($unassignedBookings as $u) {
        if (slot_overlaps($slot, $u['start'], $u['end'])) {
            $unassignedConflicts++;
        }
    }

    $userOverlap = false;
    if ($requestedUserId && !empty($userBookings)) {
        foreach ($userBookings as $ub) {
            if (slot_overlaps($slot, $ub['start'], $ub['end'])) {
                $userOverlap = true;
                break;
            }
        }
    }

    if (($occupiedDentists + $unassignedConflicts) < $scheduledDentistCount) {
        if ($userOverlap) {
            $blocked[] = $slotStr;
        } else {
            $available[] = $slotStr;
        }
    } else {
        $blocked[] = $slotStr;
    }

    $slot->add($interval);
}

$available = array_values($available);
$blocked   = array_values(array_unique($blocked));

echo json_encode([
    'times' => $available,
    'blocked' => $blocked
]);

$conn->close();
?>
