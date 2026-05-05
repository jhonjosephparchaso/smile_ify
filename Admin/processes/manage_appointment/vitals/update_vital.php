<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $appointment_id   = intval($_POST['appointment_transaction_id'] ?? 0);
    $vital_id         = intval($_POST['vitals_id'] ?? 0);
    $admin_user_id    = intval($_SESSION['user_id']);
    $body_temp        = floatval($_POST['body_temp'] ?? 0);
    $pulse_rate       = intval($_POST['pulse_rate'] ?? 0);
    $respiratory_rate = intval($_POST['respiratory_rate'] ?? 0);
    $blood_pressure   = trim($_POST['blood_pressure'] ?? '');
    $height           = floatval($_POST['height'] ?? 0);
    $weight           = floatval($_POST['weight'] ?? 0);
    $is_swelling      = ($_POST['is_swelling'] ?? 'No') === 'Yes' ? 'Yes' : 'No';
    $is_bleeding      = ($_POST['is_bleeding'] ?? 'No') === 'Yes' ? 'Yes' : 'No';
    $is_sensitive     = ($_POST['is_sensitive'] ?? 'No') === 'Yes' ? 'Yes' : 'No';

    try {
        $stmt = $conn->prepare("
            UPDATE dental_vital
            SET 
                body_temp = ?, 
                pulse_rate = ?, 
                respiratory_rate = ?, 
                blood_pressure = ?, 
                height = ?, 
                weight = ?,
                is_swelling = ?, 
                is_bleeding = ?, 
                is_sensitive = ?,
                admin_user_id = ?, 
                date_updated = NOW()
            WHERE vitals_id = ? 
            AND appointment_transaction_id = ?
            AND (
                body_temp != ? OR
                pulse_rate != ? OR
                respiratory_rate != ? OR
                blood_pressure != ? OR
                height != ? OR
                weight != ? OR
                is_swelling != ? OR
                is_bleeding != ? OR
                is_sensitive != ?
            )
        ");

        $stmt->bind_param(
            "diisddsssiiidiisddsss",
            $body_temp,
            $pulse_rate,
            $respiratory_rate,
            $blood_pressure,
            $height,
            $weight,
            $is_swelling,
            $is_bleeding,
            $is_sensitive,
            $admin_user_id,
            $vital_id,
            $appointment_id,
            $body_temp,
            $pulse_rate,
            $respiratory_rate,
            $blood_pressure,
            $height,
            $weight,
            $is_swelling,
            $is_bleeding,
            $is_sensitive
        );

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['updateSuccess'] = "Vital updated successfully!";
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("Error updating vitals: " . $e->getMessage());
        $_SESSION['updateError'] = "Failed to update vitals.";
    }

    header("Location: " . BASE_URL . "/Admin/pages/manage_appointment.php?id=" . $appointment_id . "&tab=vitals");
    exit();
}
?>
