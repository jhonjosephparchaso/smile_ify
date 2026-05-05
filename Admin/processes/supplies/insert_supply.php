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

    $name          = trim($_POST["supplyName"]);
    $quantity      = intval($_POST["quantity"]);
    $reorderLevel  = intval($_POST["reorderLevel"]);
    $status        = trim($_POST["status"]);
    $description   = $_POST["description"] ?? null;
    $category      = $_POST["category"] ?? null;
    $unit          = $_POST["unit"] ?? null;
    $expiration    = !empty($_POST["expiration_date"]) ? $_POST["expiration_date"] : null;

    if ($expiration !== null) {
        $d = DateTime::createFromFormat('Y-m-d', $expiration);
        if (!$d || $d->format('Y-m-d') !== $expiration) {
            $expiration = null;
        }
    }

    try {
        $conn->begin_transaction();

        $sql1 = "INSERT INTO supply (name, description, category, unit) 
                    VALUES (?, ?, ?, ?)";
        $stmt1 = $conn->prepare($sql1);
        if (!$stmt1) throw new Exception("Prepare failed (supply): " . $conn->error);
        $stmt1->bind_param("ssss", $name, $description, $category, $unit);
        $stmt1->execute();
        $supply_id = $stmt1->insert_id;
        $stmt1->close();

        $sql2 = "INSERT INTO branch_supply 
                    (supply_id, branch_id, quantity, reorder_level, expiration_date, status, date_created) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt2 = $conn->prepare($sql2);
        if (!$stmt2) throw new Exception("Prepare failed (branch_supply): " . $conn->error);
        $stmt2->bind_param("iiisss", $supply_id, $branch_id, $quantity, $reorderLevel, $expiration, $status);
        $stmt2->execute();
        $stmt2->close();

        if (!empty($_POST['services'])) {
            $sql3 = "INSERT INTO service_supplies (service_id, branch_id, supply_id, quantity_used, date_created)
                        VALUES (?, ?, ?, ?, NOW())";
            $stmt3 = $conn->prepare($sql3);
            if (!$stmt3) throw new Exception("Prepare failed (service_supplies): " . $conn->error);

            foreach ($_POST['services'] as $service_id) {
                $service_id = intval($service_id);
                $quantity_used = intval($_POST['quantities'][$service_id] ?? 1);
                $stmt3->bind_param("iiii", $service_id, $branch_id, $supply_id, $quantity_used);
                $stmt3->execute();
            }
            $stmt3->close();
        }

        $conn->commit();
        $_SESSION['updateSuccess'] = "Supply added successfully with service assignments!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['updateError'] = "Database error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/Admin/pages/supplies.php");
    exit;
} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$conn->close();
