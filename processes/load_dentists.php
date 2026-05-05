<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (empty($_POST['appointmentBranch'])) {
    echo '<option disabled>No branch selected</option>';
    exit;
}

if (empty($_POST['appointmentDate'])) {
    echo '<option disabled>No date selected</option>';
    exit;
}

if (empty($_POST['appointmentTime'])) {
    echo '<option disabled>No time selected</option>';
    exit;
}

$branchId = intval($_POST['appointmentBranch']);
$appointmentDate = $_POST['appointmentDate'];
$appointmentTime = $_POST['appointmentTime'];
$dayName = date('l', strtotime($appointmentDate));

$services = $_POST['appointmentServices'] ?? [];
if (!is_array($services)) {
    $services = [$services];
}
$services = array_map('intval', array_filter($services, 'strlen'));

$preassignedDentistId = $_POST['preassignedDentistId'] ?? null;
$selectedDentistId = $_POST['selectedDentistId'] ?? null;

$cleanupBuffer = 15;

$requiredServiceDuration = 0;
if (!empty($services)) {
    $placeholders = implode(',', array_fill(0, count($services), '?'));
    $sqlDur = "SELECT SUM(duration_minutes) as sum_mins FROM service WHERE service_id IN ($placeholders)";
    $stmtDur = $conn->prepare($sqlDur);
    if ($stmtDur === false) {
        $requiredServiceDuration = 0;
    } else {
        $types = str_repeat('i', count($services));
        $refs = [];
        $refs[] = & $types;
        for ($i = 0; $i < count($services); $i++) {
            $refs[] = & $services[$i];
        }
        call_user_func_array([$stmtDur, 'bind_param'], $refs);
        $stmtDur->execute();
        $resDur = $stmtDur->get_result();
        $rowDur = $resDur->fetch_assoc();
        $requiredServiceDuration = intval($rowDur['sum_mins'] ?? 0);
        $stmtDur->close();
    }
}
$requiredBlock = $requiredServiceDuration + $cleanupBuffer;

$stmt = null;

if (empty($services)) {
    $sql = "
        SELECT d.dentist_id, d.first_name, d.last_name
        FROM dentist d
        INNER JOIN dentist_branch db 
            ON d.dentist_id = db.dentist_id
        INNER JOIN dentist_schedule sch
            ON sch.dentist_id = d.dentist_id
            AND sch.branch_id = db.branch_id
            AND sch.day = ?
            AND (
                    (sch.start_time IS NULL AND sch.end_time IS NULL)
                    OR (sch.start_time <= ? AND sch.end_time > ?)
                )
        WHERE db.branch_id = ?
            AND d.status = 'Active'
        ORDER BY d.first_name ASC
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo '<option disabled>SQL prepare error: ' . htmlspecialchars($conn->error) . '</option>';
        exit;
    }

    $stmt->bind_param("sssi", $dayName, $appointmentTime, $appointmentTime, $branchId);

} else {
    $countServices = count($services);
    $placeholders = implode(',', array_fill(0, $countServices, '?'));

    $sql = "
        SELECT d.dentist_id, d.first_name, d.last_name
        FROM dentist d
        INNER JOIN dentist_branch db 
            ON d.dentist_id = db.dentist_id
        INNER JOIN dentist_schedule sch
            ON sch.dentist_id = d.dentist_id
            AND sch.branch_id = db.branch_id
            AND sch.day = ?
            AND (
                    (sch.start_time IS NULL AND sch.end_time IS NULL)
                    OR (sch.start_time <= ? AND sch.end_time > ?)
                )
        INNER JOIN dentist_service ds 
            ON d.dentist_id = ds.dentist_id
        WHERE db.branch_id = ?
            AND ds.service_id IN ($placeholders)
            AND d.status = 'Active'
        GROUP BY d.dentist_id
        HAVING COUNT(DISTINCT ds.service_id) = ?
        ORDER BY d.first_name ASC
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo '<option disabled>SQL prepare error: ' . htmlspecialchars($conn->error) . '</option>';
        exit;
    }

    $types = 'sss' . 'i' . str_repeat('i', $countServices) . 'i';
    $values = array_merge([$dayName, $appointmentTime, $appointmentTime, $branchId], $services, [$countServices]);

    $bind_names[] = $types;
    for ($i = 0; $i < count($values); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $values[$i];
        $bind_names[] = &$$bind_name;
    }

    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

if (! $stmt->execute()) {
    echo '<option disabled>SQL execute error: ' . htmlspecialchars($stmt->error) . '</option>';
    exit;
}

$res = $stmt->get_result();

$dentists = [];
while ($row = $res->fetch_assoc()) {
    $dentists[intval($row['dentist_id'])] = [
        'first_name' => $row['first_name'],
        'last_name'  => $row['last_name']
    ];
}
$stmt->close();

if (empty($dentists)) {
    echo '<option disabled>No dentist available for this time & service</option>';
    exit;
}

$sql = "
    SELECT at.appointment_time, at.dentist_id, SUM(s.duration_minutes) AS total_duration
    FROM appointment_transaction AS at
    INNER JOIN appointment_services AS ats ON at.appointment_transaction_id = ats.appointment_transaction_id
    INNER JOIN service AS s ON ats.service_id = s.service_id
    WHERE at.branch_id = ?
        AND at.appointment_date = ?
        AND at.status IN ('Booked','Approved','Confirmed')
    GROUP BY at.appointment_transaction_id, at.appointment_time, at.dentist_id
";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("is", $branchId, $appointmentDate);
$stmt2->execute();
$res2 = $stmt2->get_result();

$bookedByDentist = [];
while ($r = $res2->fetch_assoc()) {
    $did = intval($r['dentist_id']);
    $start = DateTime::createFromFormat('H:i:s', $r['appointment_time']) ?: DateTime::createFromFormat('H:i', $r['appointment_time']);
    if (!$start) continue;
    $dur = intval($r['total_duration']);
    $end = (clone $start)->modify("+".($dur + $cleanupBuffer)." minutes");
    $bookedByDentist[$did][] = ['start' => $start, 'end' => $end];
}
$stmt2->close();

$requestedStart = DateTime::createFromFormat('H:i', $appointmentTime);
if (!$requestedStart) {
    echo '<option disabled>Invalid appointment time</option>';
    exit;
}
$requestedEnd = (clone $requestedStart)->modify("+{$requiredBlock} minutes");

$finalDentists = [];

foreach ($dentists as $did => $info) {
    $conflict = false;
    if (isset($bookedByDentist[$did])) {
        foreach ($bookedByDentist[$did] as $appt) {
            if ($requestedStart < $appt['end'] && $requestedEnd > $appt['start']) {
                $conflict = true;
                break;
            }
        }
    }
    if (!$conflict) {
        $finalDentists[$did] = $info;
    }
}

if ($preassignedDentistId && !isset($finalDentists[$preassignedDentistId])) {
    $stmtExtra = $conn->prepare("
        SELECT dentist_id, first_name, last_name
        FROM dentist
        WHERE dentist_id = ? LIMIT 1
    ");
    if ($stmtExtra) {
        $stmtExtra->bind_param("i", $preassignedDentistId);
        $stmtExtra->execute();
        $resultExtra = $stmtExtra->get_result();
        if ($row = $resultExtra->fetch_assoc()) {
            $finalDentists[intval($row['dentist_id'])] = [
                'first_name' => $row['first_name'],
                'last_name'  => $row['last_name']
            ];
        }
        $stmtExtra->close();
    }
}

if (empty($finalDentists)) {
    echo '<option disabled>No dentist available for this time & service</option>';
    exit;
}

$options = '<option value="" disabled selected>Select Dentist</option>';
foreach ($finalDentists as $id => $info) {
    $dentistName = "Dr. " . htmlspecialchars($info['first_name'] . " " . $info['last_name']);
    $selected = ($selectedDentistId === $id) ? 'selected' : '';
    $options .= "<option value='$id' $selected>$dentistName</option>";
}

echo $options;

$conn->close();
?>
