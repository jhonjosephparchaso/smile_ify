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

    $supply_id      = intval($_POST["supply_id"]);
    $name           = trim($_POST["supplyName"]);
    $quantity       = intval($_POST["quantity"]);
    $reorderLevel   = intval($_POST["reorderLevel"]);
    $status         = trim($_POST["status"]);
    $description    = $_POST["description"] ?? null;
    $category       = $_POST["category"] ?? null;
    $unit           = $_POST["unit"] ?? null;
    $expirationDate = !empty($_POST["expiration_date"]) ? $_POST["expiration_date"] : null;

    if ($expirationDate !== null) {
        $d = DateTime::createFromFormat('Y-m-d', $expirationDate);
        if (!$d || $d->format('Y-m-d') !== $expirationDate) {
            $expirationDate = null;
        }
    }

    try {
        $conn->begin_transaction();
        $madeChanges = false;

        $stmtCheck1 = $conn->prepare("SELECT name, description, category, unit FROM supply WHERE supply_id = ?");
        $stmtCheck1->bind_param("i", $supply_id);
        $stmtCheck1->execute();
        $currentSupply = $stmtCheck1->get_result()->fetch_assoc();
        $stmtCheck1->close();

        if ($currentSupply && (
            $currentSupply['name'] !== $name ||
            $currentSupply['description'] !== $description ||
            $currentSupply['category'] !== $category ||
            $currentSupply['unit'] !== $unit
        )) {
            $stmt1 = $conn->prepare("UPDATE supply SET name = ?, description = ?, category = ?, unit = ? WHERE supply_id = ?");
            $stmt1->bind_param("ssssi", $name, $description, $category, $unit, $supply_id);
            $stmt1->execute();
            $stmt1->close();
            $madeChanges = true;
        }

        $stmtCheck2 = $conn->prepare("SELECT quantity, reorder_level, expiration_date, status 
                                        FROM branch_supply 
                                        WHERE supply_id = ? AND branch_id = ?");
        $stmtCheck2->bind_param("ii", $supply_id, $branch_id);
        $stmtCheck2->execute();
        $currentBranch = $stmtCheck2->get_result()->fetch_assoc();
        $stmtCheck2->close();

        if ($currentBranch && (
            (int)$currentBranch['quantity'] !== $quantity ||
            (int)$currentBranch['reorder_level'] !== $reorderLevel ||
            $currentBranch['expiration_date'] !== $expirationDate ||
            $currentBranch['status'] !== $status
        )) {
            $stmt2 = $conn->prepare("UPDATE branch_supply 
                                        SET quantity = ?, reorder_level = ?, expiration_date = ?, status = ?, date_updated = NOW()
                                        WHERE supply_id = ? AND branch_id = ?");
            $stmt2->bind_param("iissii", $quantity, $reorderLevel, $expirationDate, $status, $supply_id, $branch_id);
            $stmt2->execute();
            $stmt2->close();
            $madeChanges = true;
        }

        $currentLinks = [];
        $stmtLinks = $conn->prepare("SELECT service_id, quantity_used FROM service_supplies WHERE supply_id = ? AND branch_id = ?");
        $stmtLinks->bind_param("ii", $supply_id, $branch_id);
        $stmtLinks->execute();
        $resultLinks = $stmtLinks->get_result();
        while ($row = $resultLinks->fetch_assoc()) {
            $currentLinks[(int)$row['service_id']] = (int)$row['quantity_used'];
        }
        $stmtLinks->close();

        $newServices = array_map('intval', $_POST['services'] ?? []);

        if (!empty($newServices)) {
            foreach ($newServices as $service_id) {
                $quantity_used = intval($_POST['quantities'][$service_id] ?? 1);

                if (isset($currentLinks[$service_id])) {
                    if ($currentLinks[$service_id] !== $quantity_used) {
                        $stmtUpdate = $conn->prepare("UPDATE service_supplies 
                                                        SET quantity_used = ?, date_updated = NOW() 
                                                        WHERE service_id = ? AND supply_id = ? AND branch_id = ?");
                        $stmtUpdate->bind_param("iiii", $quantity_used, $service_id, $supply_id, $branch_id);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                        $madeChanges = true;
                    }
                } else {
                    $stmtInsert = $conn->prepare("INSERT INTO service_supplies (service_id, supply_id, branch_id, quantity_used, date_created)
                                                    VALUES (?, ?, ?, ?, NOW())");
                    $stmtInsert->bind_param("iiii", $service_id, $supply_id, $branch_id, $quantity_used);
                    $stmtInsert->execute();
                    $stmtInsert->close();
                    $madeChanges = true;
                }
            }
        }

        foreach ($currentLinks as $existingServiceId => $existingQty) {
            if (!in_array($existingServiceId, $newServices, true)) {
                $stmtDel = $conn->prepare("DELETE FROM service_supplies WHERE service_id = ? AND supply_id = ? AND branch_id = ?");
                $stmtDel->bind_param("iii", $existingServiceId, $supply_id, $branch_id);
                $stmtDel->execute();
                $stmtDel->close();
                $madeChanges = true;
            }
        }

        $conn->commit();

        if ($madeChanges) {
            $_SESSION['updateSuccess'] = "Supply updated successfully with service assignments!";
        }

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
?>
