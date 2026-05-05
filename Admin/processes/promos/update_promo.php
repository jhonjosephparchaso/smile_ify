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

    $promo_id = intval($_POST["promo_id"]);
    $status   = trim($_POST["status"]);

    try {
        $checkSql = "SELECT p.name, bp.status 
                        FROM promo p
                        JOIN branch_promo bp ON p.promo_id = bp.promo_id
                        WHERE p.promo_id = ? AND bp.branch_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $promo_id, $branch_id);
        $checkStmt->execute();
        $current = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if (!$current) {
            $_SESSION['updateError'] = "Promo not found.";
            header("Location: " . BASE_URL . "/Admin/pages/promos.php");
            exit;
        }

        if ($current['status'] !== $status) {
            $sql = "UPDATE branch_promo 
                    SET status = ?
                    WHERE branch_id = ? AND promo_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $status, $branch_id, $promo_id);
            $stmt->execute();
            $stmt->close();

            $updatePromo = $conn->prepare("UPDATE promo SET date_updated = NOW() WHERE promo_id = ?");
            $updatePromo->bind_param("i", $promo_id);
            $updatePromo->execute();
            $updatePromo->close();

            $_SESSION['updateSuccess'] = "Promo status updated successfully!";

            $branchQuery = $conn->prepare("SELECT name FROM branch WHERE branch_id = ?");
            $branchQuery->bind_param("i", $branch_id);
            $branchQuery->execute();
            $branchName = $branchQuery->get_result()->fetch_assoc()['name'] ?? 'Unknown Branch';
            $branchQuery->close();
            
            $notif_message = "The promo " . htmlspecialchars($current['name']) . " in " . htmlspecialchars($branchName) . " was set to " . htmlspecialchars($status) . ".";

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

    header("Location: " . BASE_URL . "/Admin/pages/promos.php");
    exit;
} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$conn->close();
?>
