<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $appointment_id  = intval($_POST['appointment_transaction_id'] ?? 0);
    $prescription_id = intval($_POST['prescription_id'] ?? 0);
    $admin_user_id   = intval($_SESSION['user_id']);
    $drug            = trim($_POST['drug'] ?? '');
    $frequency       = trim($_POST['frequency'] ?? '');
    $dosage          = trim($_POST['dosage'] ?? '');
    $duration        = trim($_POST['duration'] ?? '');
    $instructions    = trim($_POST['instructions'] ?? '');

    try {
        $stmt = $conn->prepare("
            UPDATE dental_prescription
            SET 
                drug = ?, 
                frequency = ?, 
                dosage = ?, 
                duration = ?, 
                instructions = ?, 
                admin_user_id = ?, 
                date_updated = NOW()
            WHERE prescription_id = ? 
            AND appointment_transaction_id = ?
            AND (
                drug != ? OR
                frequency != ? OR
                dosage != ? OR
                duration != ? OR
                instructions != ?
            )
        ");

        $stmt->bind_param(
            "sssssiiisssss", 
            $drug, 
            $frequency, 
            $dosage, 
            $duration, 
            $instructions, 
            $admin_user_id, 
            $prescription_id, 
            $appointment_id,
            $drug, 
            $frequency, 
            $dosage, 
            $duration, 
            $instructions
        );

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['updateSuccess'] = "Prescription updated successfully!";
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("Error updating prescription: " . $e->getMessage());
        $_SESSION['updateError'] = "Failed to update prescription.";
    }

    header("Location: " . BASE_URL . "/Admin/pages/manage_appointment.php?id=" . $appointment_id . "&backTab=recent&tab=prescriptions");
    exit();
}
?>
