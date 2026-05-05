<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['appointments' => []];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $sql = "
        SELECT 
            a.user_id,
            u.first_name AS patient_first,
            u.last_name AS patient_last,
            u.guardian_id,
            a.appointment_date,
            a.appointment_time,
            d.first_name AS dentist_first,
            d.last_name AS dentist_last,
            b.address AS branch_name,
            GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ', ') AS service_names
        FROM appointment_transaction a
        INNER JOIN users u ON a.user_id = u.user_id
        LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
        INNER JOIN branch b ON a.branch_id = b.branch_id
        LEFT JOIN appointment_services aps ON a.appointment_transaction_id = aps.appointment_transaction_id
        LEFT JOIN service s ON aps.service_id = s.service_id
        WHERE 
            (a.user_id = ?
                OR a.user_id IN (SELECT user_id FROM users WHERE guardian_id = ?))
            AND u.role = 'patient'
            AND a.appointment_date >= CURDATE()
            AND a.status NOT IN ('Cancelled', 'Completed')
        GROUP BY 
            a.appointment_transaction_id,
            a.appointment_date,
            a.appointment_time,
            d.first_name,
            d.last_name,
            b.address,
            u.first_name,
            u.last_name,
            u.guardian_id
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 3
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $dentistName = $row['dentist_last']
            ? 'Dr. ' . htmlspecialchars($row['dentist_last'])
            : 'an Available Dentist';

        $patientName = trim($row['patient_first'] . ' ' . $row['patient_last']);

        $isDependent = !empty($row['guardian_id']) && intval($row['guardian_id']) === $userId;

        $response['appointments'][] = [
            'for' => htmlspecialchars($patientName),
            'is_dependent' => $isDependent,
            'date' => date('F j, Y', strtotime($row['appointment_date'])),
            'time' => date('g:i A', strtotime($row['appointment_time'])),
            'dentist' => $dentistName,
            'branch' => htmlspecialchars($row['branch_name']),
            'services' => htmlspecialchars($row['service_names'] ?? 'Not specified')
        ];
    }

    $stmt->close();
    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to load appointments.',
        'details' => $e->getMessage()
    ]);
}
?>
