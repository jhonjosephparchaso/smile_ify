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
    $admin_user_id    = intval($_POST['admin_user_id'] ?? $_SESSION['user_id']);
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
            INSERT INTO dental_vital 
            (appointment_transaction_id, admin_user_id, body_temp, pulse_rate, respiratory_rate, blood_pressure, height, weight, is_swelling, is_bleeding, is_sensitive, date_created, date_updated)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->bind_param(
            "iidiisddsss",
            $appointment_id,
            $admin_user_id,
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
        $stmt->close();

        $_SESSION['updateSuccess'] = "Vitals added successfully!";
    } catch (Exception $e) {
        error_log("Error inserting vitals: " . $e->getMessage());
        $_SESSION['updateError'] = "Failed to add vitals.";
    }

    header("Location: " . BASE_URL . "/Admin/pages/manage_appointment.php?id=" . $appointment_id . "&backTab=recent&tab=vitals");
    exit();
}
?>
