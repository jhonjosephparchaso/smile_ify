<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit();
}

$thresholdDate = date('Y-m-d H:i:s', strtotime('-5 years'));

$sql = "
    UPDATE users u
    LEFT JOIN (
        SELECT 
            u.user_id,
            COALESCE(
                MAX(dt.date_created),
                MAX(at.date_created)
            ) AS last_transaction_date
        FROM users u
        LEFT JOIN appointment_transaction at ON u.user_id = at.user_id
        LEFT JOIN dental_transaction dt ON at.appointment_transaction_id = dt.appointment_transaction_id
        WHERE u.role = 'patient'
        GROUP BY u.user_id
    ) recent ON u.user_id = recent.user_id

    SET u.status = 'Inactive',
        u.date_updated = NOW()

    WHERE u.role = 'patient'
    AND recent.last_transaction_date IS NOT NULL
    AND recent.last_transaction_date < ?
    AND u.status = 'Active';
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $thresholdDate);
$stmt->execute();

$conn->close();
?>
