<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
        $_SESSION['updateError'] = "Unauthorized access.";
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }

    $service_id = $_POST["service_id"] ?? null;
    $name       = trim($_POST["serviceName"]);
    $price      = floatval($_POST["price"]);
    $duration   = intval($_POST["duration_minutes"]);
    $branches   = isset($_POST["branches"]) ? $_POST["branches"] : [];
    $requires_xray = isset($_POST["requires_xray"]) ? 1 : 0;

    try {
        $conn->begin_transaction();

        $checkSQL = "SELECT name, price, duration_minutes, requires_xray FROM service WHERE service_id = ?";
        $checkStmt = $conn->prepare($checkSQL);
        $checkStmt->bind_param("i", $service_id);
        $checkStmt->execute();
        $oldData = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        $hasChanges = false;

        if (
            $oldData['name'] !== $name ||
            floatval($oldData['price']) !== $price ||
            intval($oldData['duration_minutes']) !== $duration ||
            intval($oldData['requires_xray']) !== $requires_xray
        ) {
            $hasChanges = true;

            $sql = "UPDATE service 
                    SET name = ?, price = ?, duration_minutes = ?, requires_xray = ?, date_updated = NOW()
                    WHERE service_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdiii", $name, $price, $duration, $requires_xray, $service_id);
            $stmt->execute();
            $stmt->close();
        }

        $existingBranches = [];
        $branchCheckSQL = "SELECT branch_id FROM branch_service WHERE service_id = ?";
        $branchCheckStmt = $conn->prepare($branchCheckSQL);
        $branchCheckStmt->bind_param("i", $service_id);
        $branchCheckStmt->execute();
        $result = $branchCheckStmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $existingBranches[] = intval($row['branch_id']);
        }
        $branchCheckStmt->close();

        sort($existingBranches);
        $newBranches = array_map('intval', $branches);
        sort($newBranches);

        if ($existingBranches !== $newBranches) {
            $hasChanges = true;

            $deleteSQL = "DELETE FROM branch_service WHERE service_id = ?";
            $delStmt = $conn->prepare($deleteSQL);
            $delStmt->bind_param("i", $service_id);
            $delStmt->execute();
            $delStmt->close();

            if (!empty($newBranches)) {
                $insertSQL = "INSERT INTO branch_service (branch_id, service_id, date_created)
                                VALUES (?, ?, NOW())";
                $insStmt = $conn->prepare($insertSQL);

                foreach ($newBranches as $branch_id) {
                    $insStmt->bind_param("ii", $branch_id, $service_id);
                    $insStmt->execute();
                }
                $insStmt->close();
            }

            $conn->query("UPDATE service SET date_updated = NOW() WHERE service_id = $service_id");
        }

        $conn->commit();

        if ($hasChanges) {
            $_SESSION['updateSuccess'] = "Service updated successfully!";
        }

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['updateError'] = "Database error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/Owner/pages/services.php");
    exit;
} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$conn->close();
?>
