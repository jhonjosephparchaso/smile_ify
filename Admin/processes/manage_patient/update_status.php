<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_POST['user_id'] ?? null;
    $status  = $_POST['status'] ?? null;

    if (!$user_id || !$status) {
        $_SESSION['updateError'] = "Missing required fields.";
        header("Location: " . BASE_URL . "/Admin/pages/patients.php");
        exit;
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("UPDATE users SET status = ?, date_updated = NOW() WHERE user_id = ?");
        $stmt->bind_param("si", $status, $user_id);
        $stmt->execute();
        $stmt->close();

        $check = $conn->prepare("SELECT user_id FROM users WHERE guardian_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $dependents = $check->get_result();
        $check->close();

        if ($dependents->num_rows > 0) {
            $updateDep = $conn->prepare("UPDATE users SET status = ?, date_updated = NOW() WHERE guardian_id = ?");
            $updateDep->bind_param("si", $status, $user_id);
            $updateDep->execute();
            $updateDep->close();
        }

        $conn->commit();

        $_SESSION['updateSuccess'] = "Patient set to " . ucfirst($status) . ".";

    } catch (Exception $e) {

        $conn->rollback();
        $_SESSION['updateError'] = "Database error: " . $e->getMessage();
    }

    $conn->close();

    $redirectTab = ($status === "inactive") ? "inactive" : "registered";

    header("Location: " . BASE_URL . "/Admin/pages/patients.php?tab=" . $redirectTab);
    exit;

} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}
