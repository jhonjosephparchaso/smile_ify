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
        u.user_id,
        CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name) AS name,
        COALESCE(b.name, '-') AS branch_name,
        u.status,

        COALESCE(
            DATE_FORMAT(
                GREATEST(
                    IFNULL(MAX(dt.date_created), '0000-00-00 00:00:00'),
                    IFNULL(MAX(dt.date_updated), '0000-00-00 00:00:00'),
                    IFNULL(MAX(at.date_created), '0000-00-00 00:00:00'),
                    IFNULL(MAX(at.date_updated), '0000-00-00 00:00:00')
                ),
                '%Y-%m-%d %H:%i'
            ),
            '-'
        ) AS recent_transaction

    FROM users u
    LEFT JOIN branch b 
        ON u.branch_id = b.branch_id
    LEFT JOIN appointment_transaction at 
        ON u.user_id = at.user_id
    LEFT JOIN dental_transaction dt 
        ON at.appointment_transaction_id = dt.appointment_transaction_id

    WHERE u.role = 'patient'
    AND u.status = 'Active'

    GROUP BY 
        u.user_id, 
        u.first_name, 
        u.middle_name, 
        u.last_name, 
        u.branch_id, 
        u.status, 
        b.name

    ORDER BY u.user_id ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$patients = [];
while ($row = $result->fetch_assoc()) {
    $recent = ($row['recent_transaction'] === "0000-00-00 00:00" || $row['recent_transaction'] === null)
            ? '-'
            : $row['recent_transaction'];

    $patients[] = [
        $row['user_id'],
        $row['name'],
        $recent,
        $row['branch_name'],
        '<a href="' . BASE_URL . '/Admin/pages/manage_patient.php?id=' . $row['user_id'] . '&tab=registered" class="manage-action">Manage</a>'
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $patients]);
$conn->close();
?>
