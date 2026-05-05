<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    $branch_id       = intval($_SESSION['branch_id']);
    $announcement_id = intval($_POST["announcement_id"] ?? 0);
    $title           = trim($_POST["title"] ?? "");
    $description     = trim($_POST["description"] ?? "");
    $type            = trim($_POST["type"] ?? "General");
    $start_date      = !empty($_POST["start_date"]) ? $_POST["start_date"] : null;
    $end_date        = !empty($_POST["end_date"]) ? $_POST["end_date"] : null;
    $status          = $_POST["status"] ?? "Inactive";

    try {
        $conn->begin_transaction();

        $hasChanges = false;

        $check_sql = "SELECT title, description, type FROM announcements WHERE announcement_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $announcement_id);
        $check_stmt->execute();
        $current = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if (!$current) {
            throw new Exception("Announcement not found.");
        }

        if (
            $current['title'] !== $title ||
            $current['description'] !== $description ||
            $current['type'] !== $type
        ) {
            $sql = "UPDATE announcements
                    SET title = ?, description = ?, type = ?
                    WHERE announcement_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $title, $description, $type, $announcement_id);
            $stmt->execute();
            $stmt->close();

            $hasChanges = true;
        }

        $checkBranch = $conn->prepare("
            SELECT start_date, end_date, status 
            FROM branch_announcements 
            WHERE announcement_id = ? AND branch_id = ?
        ");
        $checkBranch->bind_param("ii", $announcement_id, $branch_id);
        $checkBranch->execute();
        $branchData = $checkBranch->get_result()->fetch_assoc();
        $checkBranch->close();

        if ($branchData) {
            if (
                $branchData['start_date'] !== $start_date ||
                $branchData['end_date'] !== $end_date ||
                $branchData['status'] !== $status
            ) {
                $updateBranch = $conn->prepare("
                    UPDATE branch_announcements
                    SET start_date = NULLIF(?, ''), 
                        end_date = NULLIF(?, ''), 
                        status = ?, 
                        date_updated = NOW()
                    WHERE announcement_id = ? AND branch_id = ?
                ");
                $updateBranch->bind_param("sssii", $start_date, $end_date, $status, $announcement_id, $branch_id);
                $updateBranch->execute();
                $updateBranch->close();

                $hasChanges = true;
            }
        } else {
            $insertBranch = $conn->prepare("
                INSERT INTO branch_announcements 
                    (branch_id, announcement_id, start_date, end_date, status, date_created)
                VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, NOW())
            ");
            $insertBranch->bind_param("iisss", $branch_id, $announcement_id, $start_date, $end_date, $status);
            $insertBranch->execute();
            $insertBranch->close();

            $hasChanges = true;
        }

        $conn->commit();

    if ($hasChanges) {
        $branchQuery = $conn->prepare("SELECT name FROM branch WHERE branch_id = ?");
        $branchQuery->bind_param("i", $branch_id);
        $branchQuery->execute();
        $branchName = $branchQuery->get_result()->fetch_assoc()['name'] ?? 'Unknown Branch';
        $branchQuery->close();

        $notif_message = "The announcement '" . htmlspecialchars($title) . "' in " . htmlspecialchars($branchName) . " was updated.";

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

        $_SESSION['updateSuccess'] = "Announcement updated successfully and owners notified!";
    }

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['updateError'] = "Database error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/Admin/pages/profile.php");
    exit;
} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$conn->close();
?>
