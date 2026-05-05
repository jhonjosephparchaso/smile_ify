<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_transaction_id = intval($_POST['appointment_transaction_id'] ?? 0);
    $dentist_id = intval($_POST['dentist_id'] ?? 0);
    $promo_id = !empty($_POST['promo_id']) ? intval($_POST['promo_id']) : null;
    $notes = trim($_POST['notes'] ?? '');
    $admin_user_id = intval($_SESSION['user_id'] ?? 0);
    $services = $_POST['appointmentServices'] ?? [];
    $quantities = $_POST['serviceQuantity'] ?? [];
    $total_payment = floatval($_POST['total_payment'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? null;
    
    $fitness_status = trim($_POST['fitness_status'] ?? '');
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $additional_payment = 0; 

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("
            INSERT INTO dental_transaction (
                appointment_transaction_id, dentist_id, promo_id, payment_method, total, additional_payment, notes,
                admin_user_id, fitness_status, diagnosis, remarks, date_created, date_updated
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->bind_param(
            "iiisddsisss",
            $appointment_transaction_id,
            $dentist_id,
            $promo_id,
            $payment_method,
            $total_payment,
            $additional_payment,
            $notes,
            $admin_user_id,
            $fitness_status,
            $diagnosis,
            $remarks
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->execute();
        $dental_transaction_id = $stmt->insert_id;
        $stmt->close();

        if ($promo_id) {
            $promoQuery = $conn->prepare("
                SELECT name, discount_type, discount_value
                FROM promo
                WHERE promo_id = ?
            ");
            $promoQuery->bind_param("i", $promo_id);
            $promoQuery->execute();
            $promoResult = $promoQuery->get_result();
            if ($promo = $promoResult->fetch_assoc()) {
                $updatePromo = $conn->prepare("
                    UPDATE dental_transaction
                    SET promo_name = ?, promo_type = ?, promo_value = ?
                    WHERE dental_transaction_id = ?
                ");
                $updatePromo->bind_param(
                    "ssdi",
                    $promo['name'],
                    $promo['discount_type'],
                    $promo['discount_value'],
                    $dental_transaction_id
                );
                $updatePromo->execute();
                $updatePromo->close();
            }
            $promoQuery->close();
        }

        if (!empty($services)) {
            $stmtService = $conn->prepare("
                INSERT INTO dental_transaction_services 
                (dental_transaction_id, service_id, service_name, service_price, quantity, additional_payment)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            if (!$stmtService) throw new Exception("Prepare failed for service insert: " . $conn->error);

            $serviceFetch = $conn->prepare("SELECT name, price FROM service WHERE service_id = ?");
            if (!$serviceFetch) throw new Exception("Prepare failed for service fetch: " . $conn->error);

            foreach ($services as $service_id) {
                $quantity = isset($quantities[$service_id]) ? intval($quantities[$service_id]) : 1;

                $serviceFetch->bind_param("i", $service_id);
                $serviceFetch->execute();
                $serviceResult = $serviceFetch->get_result();
                $service = $serviceResult->fetch_assoc();

                $service_name = $service['name'] ?? 'Unknown';
                $service_price = $service['price'] ?? 0.00;

                $extra = isset($_POST['additional_payment'][$service_id])
                        ? floatval($_POST['additional_payment'][$service_id])
                        : 0;

                $stmtService->bind_param(
                    "iisdid",
                    $dental_transaction_id,
                    $service_id,
                    $service_name,
                    $service_price,
                    $quantity,
                    $extra
                );
                $stmtService->execute();

                $serviceNames[] = $service_name;
                $servicePrices[] = number_format($service_price, 2, '.', '');
            }

            $stmtService->close();
            $serviceFetch->close();

            $total_extra = array_sum(array_map('floatval', $_POST['additional_payment'] ?? []));
            $updateExtra = $conn->prepare("
                UPDATE dental_transaction 
                SET additional_payment = ?
                WHERE dental_transaction_id = ?
            ");
            $updateExtra->bind_param("di", $total_extra, $dental_transaction_id);
            $updateExtra->execute();
            $updateExtra->close();
        }

        if (strtolower($payment_method) === 'cashless' && isset($_FILES['receipt_upload']) && $_FILES['receipt_upload']['error'] === UPLOAD_ERR_OK) {
            $getPatient = $conn->prepare("
                SELECT u.last_name 
                FROM appointment_transaction at
                JOIN users u ON u.user_id = at.user_id
                WHERE at.appointment_transaction_id = ?
            ");
            $getPatient->bind_param("i", $appointment_transaction_id);
            $getPatient->execute();
            $result = $getPatient->get_result();
            $patient = $result->fetch_assoc();
            $getPatient->close();

            $last_name_clean = $patient ? preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($patient['last_name'])) : 'unknown';

            $fileTmpPath = $_FILES['receipt_upload']['tmp_name'];
            $fileExt = strtolower(pathinfo($_FILES['receipt_upload']['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($fileExt, $allowedTypes)) {
                $_SESSION['updateError'] = "Invalid file type. Allowed: JPG, PNG, WEBP.";
                header("Location: " . BASE_URL . "/Admin/pages/manage_appointment.php?id=$appointment_transaction_id&backTab=recent&tab=transaction");
                exit();
            }

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/images/payments/cashless_payments/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileName = $dental_transaction_id . "_" . $last_name_clean . "." . $fileExt;
            $targetPath = $uploadDir . $fileName;

            foreach (glob($uploadDir . $dental_transaction_id . "_*.*") as $oldFile) {
                if (is_file($oldFile)) unlink($oldFile);
            }

            if (!move_uploaded_file($fileTmpPath, $targetPath)) {
                $_SESSION['updateError'] = "Failed to upload receipt file.";
                header("Location: " . BASE_URL . "/Admin/pages/manage_appointment.php?id=$appointment_transaction_id&backTab=recent&tab=transaction");
                exit();
            }

            $receiptPath = "/images/payments/cashless_payments/" . $fileName;
            $updateReceipt = $conn->prepare("
                UPDATE dental_transaction 
                SET cashless_receipt = ?, date_updated = NOW()
                WHERE dental_transaction_id = ?
            ");
            $updateReceipt->bind_param("si", $receiptPath, $dental_transaction_id);
            $updateReceipt->execute();
            $updateReceipt->close();
        }

        if (!empty($_FILES['xray_file']['name']) && $_FILES['xray_file']['error'] === UPLOAD_ERR_OK) {
            $getPatient = $conn->prepare("
                SELECT u.last_name 
                FROM appointment_transaction at
                JOIN users u ON u.user_id = at.user_id
                WHERE at.appointment_transaction_id = ?
            ");
            $getPatient->bind_param("i", $appointment_transaction_id);
            $getPatient->execute();
            $pat = $getPatient->get_result()->fetch_assoc();
            $getPatient->close();

            $lname = $pat ? preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($pat['last_name'])) : 'patient';

            $ext = strtolower(pathinfo($_FILES['xray_file']['name'], PATHINFO_EXTENSION));
            $cleanExt = preg_replace('/[^a-z0-9]/', '', $ext);

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/Smile-ify/images/transactions/xrays/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileName = $dental_transaction_id . "_" . $lname . "." . $cleanExt;
            $fullPath = $uploadDir . $fileName;

            foreach (glob($uploadDir . $dental_transaction_id . "_*.*") as $oldFile) unlink($oldFile);

            if (move_uploaded_file($_FILES['xray_file']['tmp_name'], $fullPath)) {
                $relativePath = "images/transactions/xrays/" . $fileName;
                $saveXray = $conn->prepare("
                    UPDATE dental_transaction SET xray_file = ?, date_updated = NOW()
                    WHERE dental_transaction_id = ?
                ");
                $saveXray->bind_param("si", $relativePath, $dental_transaction_id);
                $saveXray->execute();
                $saveXray->close();
            }
        }

        $conn->commit();
        $_SESSION['updateSuccess'] = "Transaction added successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        error_log("INSERT TRANSACTION ERROR: " . $e->getMessage() . " | SQL Error: " . $conn->error);
        $_SESSION['updateError'] = "Failed to add transaction. Check error_log for details.";
    }

    header("Location: " . BASE_URL . "/Admin/pages/manage_appointment.php?id=$appointment_transaction_id&backTab=recent&tab=transaction");
    exit();
}
?>
