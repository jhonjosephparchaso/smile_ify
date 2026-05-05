<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $sql = "
        SELECT 
            d.dentist_id,
            d.gender,
            CONCAT(d.first_name, ' ', d.last_name) AS full_name,
            d.email,
            d.contact_number,
            d.contact_number_iv,
            d.contact_number_tag,
            d.profile_image,
            GROUP_CONCAT(DISTINCT b.name ORDER BY b.name SEPARATOR ', ') AS branch_name,
            GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS services
        FROM dentist d
        LEFT JOIN dentist_branch db ON d.dentist_id = db.dentist_id
        LEFT JOIN branch b ON db.branch_id = b.branch_id
        LEFT JOIN dentist_service ds ON d.dentist_id = ds.dentist_id
        LEFT JOIN service s ON ds.service_id = s.service_id AND s.name != 'Dental Certificate'
        WHERE d.status = 'Active'
        GROUP BY d.dentist_id
        ORDER BY d.last_name ASC
    ";

    $result = $conn->query($sql);
    $dentists = [];

    while ($row = $result->fetch_assoc()) {

        if (!empty($row['contact_number']) && !empty($row['contact_number_iv']) && !empty($row['contact_number_tag'])) {
            $row['contact_number'] = decryptField(
                $row['contact_number'],
                $row['contact_number_iv'],
                $row['contact_number_tag'],
                $ENCRYPTION_KEY
            );
        }

        unset($row['contact_number_iv'], $row['contact_number_tag']);

        $row['dentist_name'] = 'Dr. ' . $row['full_name'];
        $row['profile_image'] = !empty($row['profile_image'])
            ? BASE_URL . '/images/dentists/profile/' . $row['profile_image']
            : BASE_URL . '/images/dentists/profile/default_avatar.jpg';

        $row['branch_name'] = $row['branch_name'] ?: 'N/A';
        $row['services'] = $row['services'] ?: 'No assigned services';

        $dentists[$row['dentist_id']] = $row;
        $dentists[$row['dentist_id']]['schedule'] = [];
    }

    $sqlSchedule = "
        SELECT 
            ds.dentist_id,
            ds.day,
            ds.start_time,
            ds.end_time,
            b.name AS branch_name
        FROM dentist_schedule ds
        LEFT JOIN branch b ON ds.branch_id = b.branch_id
        ORDER BY ds.dentist_id, FIELD(ds.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
    ";

    $scheduleResult = $conn->query($sqlSchedule);

    while ($s = $scheduleResult->fetch_assoc()) {

        $dentistId = $s['dentist_id'];

        if (!isset($dentists[$dentistId])) continue;

        $dentists[$dentistId]['schedule'][] = [
            'day' => $s['day'],
            'branch' => $s['branch_name'] ?: 'N/A',
            'time' => date('h:i A', strtotime($s['start_time'])) . " - " . date('h:i A', strtotime($s['end_time']))
        ];
    }

    echo json_encode([
        'success' => true,
        'dentists' => array_values($dentists)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
