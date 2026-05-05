<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $branch_id = $_SESSION['branch_id'] ?? null;

    if (!$branch_id) {
        $_SESSION['updateError'] = "Branch not set. Please log in again.";
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }

    $service_id = intval($_POST["service_id"]);
    $status     = trim($_POST["status"]);

    if (!$service_id || empty($status)) {
        $_SESSION['updateError'] = "Invalid request.";
        header("Location: " . BASE_URL . "/Admin/pages/services.php");
        exit;
    }

    try {
        $checkSql = "SELECT status FROM branch_service WHERE branch_id = ? AND service_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $branch_id, $service_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $current = $result->fetch_assoc();
        $checkStmt->close();

        if (!$current) {
            $_SESSION['updateError'] = "Service not found for this branch.";
            header("Location: " . BASE_URL . "/Admin/pages/services.php");
            exit;
        }

        if ($current['status'] !== $status) {
            $updateSQL = "UPDATE branch_service 
                            SET status = ?, date_updated = NOW() 
                            WHERE branch_id = ? AND service_id = ?";
            $updateStmt = $conn->prepare($updateSQL);
            $updateStmt->bind_param("sii", $status, $branch_id, $service_id);
            $updateStmt->execute();
            $updateStmt->close();

            $updateServiceSQL = "UPDATE service SET date_updated = NOW() WHERE service_id = ?";
            $updateServiceStmt = $conn->prepare($updateServiceSQL);
            $updateServiceStmt->bind_param("i", $service_id);
            $updateServiceStmt->execute();
            $updateServiceStmt->close();

            $_SESSION['updateSuccess'] = "Service status updated successfully!";

            $branch_name = "";
            $branchQuery = $conn->prepare("SELECT name FROM branch WHERE branch_id = ?");
            $branchQuery->bind_param("i", $branch_id);
            $branchQuery->execute();
            $branchResult = $branchQuery->get_result();
            if ($branchResult->num_rows > 0) {
                $branchRow = $branchResult->fetch_assoc();
                $branch_name = $branchRow['name'];
            }
            $branchQuery->close();

            $service_name = "";
            $serviceQuery = $conn->prepare("SELECT name FROM service WHERE service_id = ?");
            $serviceQuery->bind_param("i", $service_id);
            $serviceQuery->execute();
            $serviceResult = $serviceQuery->get_result();
            if ($serviceResult->num_rows > 0) {
                $serviceRow = $serviceResult->fetch_assoc();
                $service_name = $serviceRow['name'];
            }
            $serviceQuery->close();

            $notif_message = "The service " . htmlspecialchars($service_name) . " in " . htmlspecialchars($branch_name) . " was set to " . htmlspecialchars($status) . ".";

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
        }

    } catch (Exception $e) {
        $_SESSION['updateError'] = "Database error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/Admin/pages/services.php");
    exit;
} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$conn->close();
?>
