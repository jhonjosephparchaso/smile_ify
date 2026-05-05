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

    $name       = trim($_POST["serviceName"]);
    $price      = floatval($_POST["price"]);
    $duration   = intval($_POST["duration_minutes"]);
    $branches   = isset($_POST["branches"]) ? $_POST["branches"] : [];
    $requires_xray = isset($_POST["requires_xray"]) ? 1 : 0;

    try {
        $conn->begin_transaction();

        $sql = "INSERT INTO service (name, price, duration_minutes, requires_xray, date_created, date_updated)
                VALUES (?, ?, ?, ?, NOW(), NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed (service): " . $conn->error);
        }

        $stmt->bind_param("sdii", $name, $price, $duration, $requires_xray);
        $stmt->execute();
        $service_id = $stmt->insert_id;
        $stmt->close();

        if (!empty($branches)) {
            $sql2 = "INSERT INTO branch_service (branch_id, service_id, date_created)
                        VALUES (?, ?, NOW())";

            $stmt2 = $conn->prepare($sql2);
            if (!$stmt2) throw new Exception("Prepare failed (branch_service): " . $conn->error);

            foreach ($branches as $branch_id) {
                $branch_id = intval($branch_id);
                $stmt2->bind_param("ii", $branch_id, $service_id);
                $stmt2->execute();
            }

            $stmt2->close();
        }

        $conn->commit();

        $_SESSION['updateSuccess'] = "Service added successfully!";
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
