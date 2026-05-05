<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$unreadCount = 0;
$notifications = [];

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT 
            n.notification_id,
            n.message,
            n.is_read,
            n.date_created,
            n.appointment_transaction_id,
            at.status AS appointment_status
        FROM notifications n
        LEFT JOIN appointment_transaction at
            ON at.appointment_transaction_id = n.appointment_transaction_id
        WHERE n.user_id = ?
        ORDER BY n.date_created DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if (!$row['is_read']) {
            $unreadCount++;
        }
    }
}
?>
