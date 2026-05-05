<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["data" => []]);
    exit();
}

$branch_id = $_SESSION['branch_id'];

$sql = "
    SELECT 
        a.appointment_transaction_id,
        CONCAT(u.first_name, ' ', u.last_name) AS patient,
        b.name AS branch,
        CONCAT('Dr. ', d.last_name, ', ', d.first_name, ' ', IFNULL(d.middle_name, '')) AS dentist,
        a.appointment_date,
        a.appointment_time,
        a.date_created,
        a.status,
        u.user_id
    FROM appointment_transaction a
    JOIN users u ON a.user_id = u.user_id
    LEFT JOIN branch b ON a.branch_id = b.branch_id
    LEFT JOIN dentist d ON a.dentist_id = d.dentist_id
    WHERE a.branch_id = ?
        AND u.role = 'patient'
        AND a.status = 'booked'
    GROUP BY a.appointment_transaction_id
    ORDER BY a.date_created DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = [
        $row['appointment_transaction_id'],
        $row['patient'],
        $row['dentist'] ?: 'Available Dentist',
        $row['appointment_date'],
        substr($row['appointment_time'], 0, 5),
        $row['date_created'],
        '<a href="' . BASE_URL . '/Admin/pages/manage_appointment.php?id=' . $row['appointment_transaction_id'] . '&backTab=recent&tab=dental_transactions" class="manage-action">Manage</a>'
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $bookings]);
$conn->close();
?>
