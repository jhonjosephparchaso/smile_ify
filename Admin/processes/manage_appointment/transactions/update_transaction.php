<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $dental_transaction_id = intval($_POST['dental_transaction_id'] ?? 0);
    $appointment_transaction_id = intval($_POST['appointment_transaction_id'] ?? 0);
    $dentist_id = intval($_POST['dentist_id'] ?? 0);
    $promo_id = !empty($_POST['promo_id']) ? intval($_POST['promo_id']) : null;
    $payment_method = $_POST['payment_method'] ?? null;
    $notes = trim($_POST['notes'] ?? '');
    $total_payment = floatval($_POST['total_payment'] ?? 0);
    $admin_user_id = intval($_SESSION['user_id']);
    $services = $_POST['appointmentServices'] ?? [];
    $quantities = $_POST['serviceQuantity'] ?? [];
    $fitness_status = trim($_POST['fitness_status'] ?? '');
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    try {
        $stmt = $conn->prepare("
            SELECT dentist_id, promo_id, payment_method, total, additional_payment,
                    notes, fitness_status, diagnosis, remarks, cashless_receipt, xray_file
            FROM dental_transaction 
            WHERE dental_transaction_id = ? AND appointment_transaction_id = ?
        ");
        $stmt->bind_param("ii", $dental_transaction_id, $appointment_transaction_id);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$existing) {
            $_SESSION['updateError'] = "Transaction not found.";
            header("Location: " . BASE_URL . "/Admin/pages/manage_appointment.php?id=" . $appointment_transaction_id);
            exit();
        }

        $hasChanges =
            intval($existing['dentist_id']) !== $dentist_id ||
            (intval($existing['promo_id']) !== intval($promo_id)) ||
            ($existing['payment_method'] !== $payment_method) ||
            floatval($existing['total']) !== $total_payment ||
            trim($existing['notes']) !== $notes ||
            trim($existing['fitness_status']) !== $fitness_status ||
            trim($existing['diagnosis']) !== $diagnosis ||
            trim($existing['remarks']) !== $remarks;

        $servicesChanged = false;
        $oldServices = [];
        $resOld = $conn->query("SELECT service_id, quantity FROM dental_transaction_services WHERE dental_transaction_id = {$dental_transaction_id}");
        while ($row = $resOld->fetch_assoc()) {
            $oldServices[intval($row['service_id'])] = intval($row['quantity']);
        }

        if (count($oldServices) !== count($services)) {
            $servicesChanged = true;
        } else {
            foreach ($services as $svcId) {
                $svcId = intval($svcId);
                if (!isset($oldServices[$svcId])) { $servicesChanged = true; break; }
                $oldQty = $oldServices[$svcId];
                $newQty = isset($quantities[$svcId]) ? intval($quantities[$svcId]) : 1;
                if ($oldQty !== $newQty) { $servicesChanged = true; break; }
            }
        }

        $xrayChanged = false;
        $remove_xray_flag = ($_POST['remove_xray'] ?? "0") === "1";
        if ($remove_xray_flag) $xrayChanged = true;
        if (!empty($_FILES['xray_file']['name'])) $xrayChanged = true;

        $conn->begin_transaction();

        $removed_receipt = $_POST['removed_receipt'] ?? "0";
        $receiptChanged = false;
        if ($removed_receipt !== "0") {
            $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify' . $removed_receipt;
            if (is_file($oldPath)) unlink($oldPath);
            $clr = $conn->prepare("UPDATE dental_transaction SET cashless_receipt = NULL, date_updated = NOW() WHERE dental_transaction_id = ?");
            $clr->bind_param("i", $dental_transaction_id);
            $clr->execute();
            $clr->close();
            $receiptChanged = true;
        }

        if ($hasChanges) {
            $promo_name_snapshot = null;
            $promo_type_snapshot = null;
            $promo_value_snapshot = null;

            if (!empty($promo_id)) {
                $p = $conn->prepare("SELECT name, discount_type, discount_value FROM promo WHERE promo_id = ?");
                $p->bind_param("i", $promo_id);
                $p->execute();
                $promo = $p->get_result()->fetch_assoc();
                $p->close();
                if ($promo) {
                    $promo_name_snapshot = $promo['name'];
                    $promo_type_snapshot = $promo['discount_type'];
                    $promo_value_snapshot = floatval($promo['discount_value']);
                }
            }

            $additional_payment_placeholder = 0.0;

            $u = $conn->prepare("
                UPDATE dental_transaction
                SET dentist_id = ?, promo_id = ?, payment_method = ?, total = ?, additional_payment = ?,
                    notes = ?, fitness_status = ?, diagnosis = ?, remarks = ?, admin_user_id = ?, 
                    date_updated = NOW(), promo_name = ?, promo_type = ?, promo_value = ?
                WHERE dental_transaction_id = ? AND appointment_transaction_id = ?
            ");
            $types = "iisddssssissdii";
            $u->bind_param(
                $types,
                $dentist_id,
                $promo_id,
                $payment_method,
                $total_payment,
                $additional_payment_placeholder,
                $notes,
                $fitness_status,
                $diagnosis,
                $remarks,
                $admin_user_id,
                $promo_name_snapshot,
                $promo_type_snapshot,
                $promo_value_snapshot,
                $dental_transaction_id,
                $appointment_transaction_id
            );
            $u->execute();
            $u->close();
        }

        if ($servicesChanged) {
            $del = $conn->prepare("DELETE FROM dental_transaction_services WHERE dental_transaction_id = ?");
            $del->bind_param("i", $dental_transaction_id);
            $del->execute();
            $del->close();

            if (!empty($services)) {
                $serviceStmt = $conn->prepare("
                    INSERT INTO dental_transaction_services
                    (dental_transaction_id, service_id, service_name, service_price, quantity, additional_payment)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $fetchStmt = $conn->prepare("SELECT name, price FROM service WHERE service_id = ?");
                foreach ($services as $service_id) {
                    $service_id = intval($service_id);
                    $quantity = isset($quantities[$service_id]) ? intval($quantities[$service_id]) : 1;

                    $fetchStmt->bind_param("i", $service_id);
                    $fetchStmt->execute();
                    $res = $fetchStmt->get_result()->fetch_assoc();

                    $service_name  = $res['name'] ?? 'Unknown';
                    $service_price = floatval($res['price'] ?? 0.00);

                    $extra = isset($_POST['additional_payment'][$service_id]) ? floatval($_POST['additional_payment'][$service_id]) : 0.0;

                    $serviceStmt->bind_param("iisdid",
                        $dental_transaction_id,
                        $service_id,
                        $service_name,
                        $service_price,
                        $quantity,
                        $extra
                    );
                    $serviceStmt->execute();
                }
                $fetchStmt->close();
                $serviceStmt->close();
            }
        } else {
            if (!empty($services)) {
                $upd = $conn->prepare("
                    UPDATE dental_transaction_services
                    SET quantity = ?, additional_payment = ?
                    WHERE dental_transaction_id = ? AND service_id = ?
                ");
                foreach ($services as $service_id) {
                    $service_id = intval($service_id);
                    $quantity = isset($quantities[$service_id]) ? intval($quantities[$service_id]) : 1;
                    $extra = isset($_POST['additional_payment'][$service_id]) ? floatval($_POST['additional_payment'][$service_id]) : 0.0;
                    $upd->bind_param("didi", $quantity, $extra, $dental_transaction_id, $service_id);
                    $upd->execute();
                }
                $upd->close();
            }
        }

        $sumStmt = $conn->prepare("
            SELECT IFNULL(SUM(additional_payment), 0) AS total_extra
            FROM dental_transaction_services
            WHERE dental_transaction_id = ?
        ");
        $sumStmt->bind_param("i", $dental_transaction_id);
        $sumStmt->execute();
        $sumRes = $sumStmt->get_result()->fetch_assoc();
        $sumStmt->close();

        $total_extra = floatval($sumRes['total_extra'] ?? 0.0);

        $updateExtra = $conn->prepare("UPDATE dental_transaction SET additional_payment = ?, date_updated = NOW() WHERE dental_transaction_id = ?");
        $updateExtra->bind_param("di", $total_extra, $dental_transaction_id);
        $updateExtra->execute();
        $updateExtra->close();

        if ($hasChanges) {
            $u2 = $conn->prepare("
                UPDATE dental_transaction
                SET additional_payment = ?, date_updated = NOW()
                WHERE dental_transaction_id = ?
            ");
            $u2->bind_param("di", $total_extra, $dental_transaction_id);
            $u2->execute();
            $u2->close();
        }

        if (strtolower($payment_method) === 'cashless' &&
            isset($_FILES['receipt_upload']) &&
            $_FILES['receipt_upload']['error'] === UPLOAD_ERR_OK) {

            $receiptChanged = true;

            $gp = $conn->prepare("
                SELECT u.last_name 
                FROM appointment_transaction at
                JOIN users u ON u.user_id = at.user_id
                WHERE at.appointment_transaction_id=?
            ");
            $gp->bind_param("i", $appointment_transaction_id);
            $gp->execute();
            $p = $gp->get_result()->fetch_assoc();
            $gp->close();
            $last_name_clean = $p ? preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($p['last_name'])) : 'unknown';

            $fileTmpPath = $_FILES['receipt_upload']['tmp_name'];
            $fileExt = strtolower(pathinfo($_FILES['receipt_upload']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($fileExt, $allowed)) {
                $_SESSION['updateError'] = "Invalid file type.";
                header("Location: " . BASE_URL . "/Admin/pages/manage_appointment.php?id={$appointment_transaction_id}&backTab=recent&tab=transaction");
                exit();
            }

            $dir = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/images/payments/cashless_payments/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            foreach (glob($dir . $dental_transaction_id . "_*.*") as $old) {
                if (is_file($old)) unlink($old);
            }

            $fileName = "{$dental_transaction_id}_{$last_name_clean}.{$fileExt}";
            $target = $dir . $fileName;
            move_uploaded_file($fileTmpPath, $target);

            $path = "/images/payments/cashless_payments/" . $fileName;
            $ur = $conn->prepare("UPDATE dental_transaction SET cashless_receipt = ?, date_updated = NOW() WHERE dental_transaction_id = ?");
            $ur->bind_param("si", $path, $dental_transaction_id);
            $ur->execute();
            $ur->close();
        }

        if (strtolower($payment_method) === 'cash') {
            $rc = $conn->prepare("SELECT cashless_receipt FROM dental_transaction WHERE dental_transaction_id = ?");
            $rc->bind_param("i", $dental_transaction_id);
            $rc->execute();
            $receipt = $rc->get_result()->fetch_assoc();
            $rc->close();

            if (!empty($receipt['cashless_receipt'])) {
                $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify' . $receipt['cashless_receipt'];
                if (is_file($oldPath)) unlink($oldPath);
                $clr = $conn->prepare("UPDATE dental_transaction SET cashless_receipt = NULL, date_updated = NOW() WHERE dental_transaction_id = ?");
                $clr->bind_param("i", $dental_transaction_id);
                $clr->execute();
                $clr->close();
                $receiptChanged = true;
            }
        }

        if ($remove_xray_flag) {
            $cur = $conn->prepare("SELECT xray_file FROM dental_transaction WHERE dental_transaction_id = ?");
            $cur->bind_param("i", $dental_transaction_id);
            $cur->execute();
            $curRes = $cur->get_result()->fetch_assoc();
            $cur->close();

            if (!empty($curRes['xray_file'])) {
                $full = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/' . $curRes['xray_file'];
                if (is_file($full)) unlink($full);
            }

            $clrX = $conn->prepare("UPDATE dental_transaction SET xray_file = NULL, date_updated = NOW() WHERE dental_transaction_id = ?");
            $clrX->bind_param("i", $dental_transaction_id);
            $clrX->execute();
            $clrX->close();
        }

        if (isset($_FILES['xray_file']) && is_uploaded_file($_FILES['xray_file']['tmp_name'])) {
            $gp = $conn->prepare("
                SELECT u.last_name 
                FROM appointment_transaction at
                JOIN users u ON u.user_id = at.user_id
                WHERE at.appointment_transaction_id=?
            ");
            $gp->bind_param("i", $appointment_transaction_id);
            $gp->execute();
            $p = $gp->get_result()->fetch_assoc();
            $gp->close();
            $last_name_clean = $p ? preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($p['last_name'])) : 'patient';

            $ext = strtolower(pathinfo($_FILES['xray_file']['name'], PATHINFO_EXTENSION));
            $cleanExt = preg_replace('/[^a-z0-9]/', '', $ext);

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/Smile-ify/images/transactions/xrays/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            foreach (glob($uploadDir . $dental_transaction_id . "_*.*") as $oldFile) {
                if (is_file($oldFile)) unlink($oldFile);
            }

            $fileName = $dental_transaction_id . "_" . $last_name_clean . "." . $cleanExt;
            $fullPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['xray_file']['tmp_name'], $fullPath)) {
                $relativePath = "images/transactions/xrays/" . $fileName;
                $saveXray = $conn->prepare("UPDATE dental_transaction SET xray_file = ?, date_updated = NOW() WHERE dental_transaction_id = ?");
                $saveXray->bind_param("si", $relativePath, $dental_transaction_id);
                $saveXray->execute();
                $saveXray->close();
            }
        }

        $conn->commit();

        if ($hasChanges || $servicesChanged || $receiptChanged || $xrayChanged) {
            $_SESSION['updateSuccess'] = "Transaction updated successfully!";
        }

    } catch (Exception $e) {
        if ($conn->in_transaction()) $conn->rollback();
        error_log("UPDATE TRANSACTION ERROR: " . $e->getMessage() . " | SQL Error: " . $conn->error);
        $_SESSION['updateError'] = "Failed to update transaction. Check error_log for details.";
    }

    header("Location: " . BASE_URL . "/Admin/pages/manage_appointment.php?id={$appointment_transaction_id}&backTab=recent&tab=transaction");
    exit();
}
?>
