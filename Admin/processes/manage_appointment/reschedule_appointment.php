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
    $branch_id        = intval($_POST['appointmentBranch'] ?? 0);
    $appointmentServices = $_POST['appointmentServices'] ?? [];
    $dentist_id       = !empty($_POST['appointmentDentist']) && $_POST['appointmentDentist'] !== 'none'
                        ? intval($_POST['appointmentDentist'])
                        : null;
    $appointment_date = $_POST['appointmentDate'] ?? null;
    $appointment_time = $_POST['appointmentTime'] ?? null;

    if (!$appointment_id || !$branch_id || empty($appointmentServices) || !$appointment_date || !$appointment_time) {
        $_SESSION['updateError'] = "Missing required fields.";
        header("Location: " . BASE_URL . "/Admin/pages/patients.php");
        exit();
    }

    try {
        $conn->begin_transaction();

        $userQuery = $conn->prepare("SELECT user_id FROM appointment_transaction WHERE appointment_transaction_id = ?");
        $userQuery->bind_param("i", $appointment_id);
        $userQuery->execute();
        $userResult = $userQuery->get_result();
        $userRow = $userResult->fetch_assoc();
        $user_id = $userRow ? intval($userRow['user_id']) : 0;
        $userQuery->close();

        $sql = "
            UPDATE appointment_transaction
            SET branch_id = ?, 
                dentist_id = ?, 
                appointment_date = ?, 
                appointment_time = ?, 
                date_updated = NOW()
            WHERE appointment_transaction_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissi",
            $branch_id,
            $dentist_id,
            $appointment_date,
            $appointment_time,
            $appointment_id
        );
        $stmt->execute();
        $stmt->close();

        $delete = $conn->prepare("DELETE FROM appointment_services WHERE appointment_transaction_id = ?");
        $delete->bind_param("i", $appointment_id);
        $delete->execute();
        $delete->close();

        $sql = "
            INSERT INTO appointment_services 
            (appointment_transaction_id, service_id, quantity)
            VALUES (?, ?, 1)
        ";
        $stmt = $conn->prepare($sql);

        foreach ($appointmentServices as $service_id) {
            $sid = intval($service_id);
            $stmt->bind_param("ii", $appointment_id, $sid);
            $stmt->execute();
        }

        $stmt->close();

        $formattedDate = date("F j, Y", strtotime($appointment_date));
        $formattedTime = date("g:i A", strtotime($appointment_time));

        $msg = "Your appointment has been rescheduled to $formattedDate at $formattedTime.";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif->bind_param("is", $user_id, $msg);
        $notif->execute();
        $notif->close();

        $guardianQuery = $conn->prepare("SELECT guardian_id FROM users WHERE user_id = ?");
        $guardianQuery->bind_param("i", $user_id);
        $guardianQuery->execute();
        $guardianRes = $guardianQuery->get_result();
        $guardianRow = $guardianRes->fetch_assoc();
        $guardianQuery->close();

        if (!empty($guardianRow['guardian_id'])) {
            $guardianId = intval($guardianRow['guardian_id']);
            $guardianMessage = 
                "The appointment for your dependent has been rescheduled to $formattedDate at $formattedTime.";

            $guardianNotif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $guardianNotif->bind_param("is", $guardianId, $guardianMessage);
            $guardianNotif->execute();
            $guardianNotif->close();
        }

        $conn->commit();

        $_SESSION['updateSuccess'] = "Appointment rescheduled successfully!";
        header("Location: " . BASE_URL . "/Admin/pages/patients.php");
        exit();

    } catch (Exception $e) {

        $conn->rollback();
        error_log("Error rescheduling appointment: " . $e->getMessage());

        $_SESSION['updateError'] = "Failed to reschedule appointment. Please try again.";
        header("Location: " . BASE_URL . "/Admin/pages/patients.php");
        exit();
    }
}

$conn->close();
?>
