<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';


if (isset($_SESSION['user_id']) && isset($_POST['notification_id'])) {
    $userId = $_SESSION['user_id'];
    $notifId = intval($_POST['notification_id']);

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notifId, $userId);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    http_response_code(400);
    echo "missing data";
}
?>
