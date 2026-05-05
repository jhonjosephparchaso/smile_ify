<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['updateError'] = "Unauthorized access.";
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

if (!isset($_SESSION['branch_id'])) {
    $_SESSION['updateError'] = "Branch ID not found. Please log in again.";
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$title        = trim($_POST["title"] ?? "");
$description  = trim($_POST["description"] ?? "");
$type         = trim($_POST["type"] ?? "General");
$start_date   = $_POST["start_date"] ?? null;
$end_date     = $_POST["end_date"] ?? null;
$status       = $_POST["status"] ?? "Inactive";
$branch_id    = intval($_SESSION['branch_id']);

try {
    $conn->begin_transaction();

    $sql = "INSERT INTO announcements (title, description, type) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sss", $title, $description, $type);
    $stmt->execute();
    $announcement_id = $stmt->insert_id;
    $stmt->close();

    $sql2 = "
        INSERT INTO branch_announcements 
            (branch_id, announcement_id, start_date, end_date, status, date_created)
        VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, NOW())
    ";
    $stmt2 = $conn->prepare($sql2);
    if (!$stmt2) {
        throw new Exception("Prepare failed (branch link): " . $conn->error);
    }
    $stmt2->bind_param("iisss", $branch_id, $announcement_id, $start_date, $end_date, $status);
    $stmt2->execute();
    $stmt2->close();

    $branchQuery = $conn->prepare("SELECT name FROM branch WHERE branch_id = ?");
    $branchQuery->bind_param("i", $branch_id);
    $branchQuery->execute();
    $branchName = $branchQuery->get_result()->fetch_assoc()['name'] ?? 'Unknown Branch';
    $branchQuery->close();

    $notif_message = "A new announcement titled '" . htmlspecialchars($title) . "' was added for " . htmlspecialchars($branchName) . ".";

    $getOwners = $conn->prepare("SELECT user_id FROM users WHERE role = 'owner' AND status = 'Active'");
    $getOwners->execute();
    $ownersResult = $getOwners->get_result();

    if ($ownersResult->num_rows > 0) {
        $notifSQL = "INSERT INTO notifications (user_id, message, is_read, date_created)
                        VALUES (?, ?, 0, NOW())";
        $notifStmt = $conn->prepare($notifSQL);

        while ($owner = $ownersResult->fetch_assoc()) {
            $notifStmt->bind_param("is", $owner['user_id'], $notif_message);
            $notifStmt->execute();
        }

        $notifStmt->close();
    }
    $getOwners->close();

    $conn->commit();
    $_SESSION['updateSuccess'] = "Announcement added successfully and owners notified!";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['updateError'] = "Database error: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/Admin/pages/profile.php");
exit;

$conn->close();
?>
