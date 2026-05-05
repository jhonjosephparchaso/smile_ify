<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $original_user_id = intval($_POST['user_id'] ?? 0);
    $bookingType      = $_POST['bookingType'] ?? "self";

    $branch_id = intval($_POST['appointmentBranch'] ?? 0);

    $final_user_id = $original_user_id;

    if ($bookingType === "child") {

        $check = $conn->prepare("SELECT guardian_id FROM users WHERE user_id = ?");
        $check->bind_param("i", $original_user_id);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();
        $check->close();

        if (!empty($res['guardian_id'])) {
            $_SESSION['updateError'] = "Dependent accounts cannot register another child.";
            header("Location: " . BASE_URL . "/Admin/pages/patients.php");
            exit();
        }

        $childFirst  = trim($_POST['childFirstName'] ?? "");
        $childLast   = trim($_POST['childLastName'] ?? "");
        $childGender = trim($_POST['childGender'] ?? "");
        $childDob    = trim($_POST['childDob'] ?? "");
        $relationship = trim($_POST['relationship'] ?? "");

        [$cdob_enc, $cdob_iv, $cdob_tag] = encryptField($childDob);

        $insertChild = $conn->prepare("
            INSERT INTO users 
            (first_name, last_name, gender,
            date_of_birth, date_of_birth_iv, date_of_birth_tag,
            guardian_id, relationship, status, role, branch_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'patient', ?)
        ");

        $insertChild->bind_param(
            "ssssssisi",
            $childFirst,
            $childLast,
            $childGender,
            $cdob_enc,
            $cdob_iv,
            $cdob_tag,
            $original_user_id,
            $relationship,
            $branch_id
        );

        $insertChild->execute();
        $final_user_id = $insertChild->insert_id;
        $insertChild->close();
    }

    $appointmentServices = $_POST['appointmentServices'] ?? [];
    $dentist_id       = !empty($_POST['appointmentDentist']) && $_POST['appointmentDentist'] !== 'none'
                        ? intval($_POST['appointmentDentist'])
                        : null;
    $appointment_date = $_POST['appointmentDate'] ?? null;
    $appointment_time = $_POST['appointmentTime'] ?? null;
    $notes            = trim($_POST['notes'] ?? '');

    if (!$final_user_id || !$branch_id || empty($appointmentServices) || !$appointment_date || !$appointment_time) {
        $_SESSION['updateError'] = "Missing required fields.";
        header("Location: " . BASE_URL . "/Admin/pages/patients.php");
        exit();
    }

    try {
        $conn->begin_transaction();

        $sql = "
            INSERT INTO appointment_transaction 
            (user_id, branch_id, dentist_id, appointment_date, appointment_time, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Booked')
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiisss",
            $final_user_id,
            $branch_id,
            $dentist_id,
            $appointment_date,
            $appointment_time,
            $notes
        );
        $stmt->execute();
        $appointment_id = $stmt->insert_id;
        $stmt->close();

        if (!empty($appointmentServices)) {
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
        }

        $update = $conn->prepare("UPDATE users SET status = 'Active', date_updated = NOW() WHERE user_id = ?");
        $update->bind_param("i", $final_user_id);
        $update->execute();
        $update->close();

        $msg = "Your appointment on $appointment_date at $appointment_time was successfully booked!";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif->bind_param("is", $final_user_id, $msg);
        $notif->execute();
        $notif->close();

        $guardianQuery = $conn->prepare("SELECT guardian_id FROM users WHERE user_id = ?");
        $guardianQuery->bind_param("i", $final_user_id);
        $guardianQuery->execute();
        $guardianResult = $guardianQuery->get_result()->fetch_assoc();
        $guardianQuery->close();

        if (!empty($guardianResult['guardian_id'])) {
            $guardian_id = intval($guardianResult['guardian_id']);

            $guardianMsg = "Your dependent's appointment on $appointment_date at $appointment_time has been booked.";
            $notifGuardian = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notifGuardian->bind_param("is", $guardian_id, $guardianMsg);
            $notifGuardian->execute();
            $notifGuardian->close();
        }

        $conn->commit();

        $_SESSION['updateSuccess'] = "Appointment booked successfully!";
        header("Location: " . BASE_URL . "/Admin/pages/patients.php");
        exit();

    } catch (Exception $e) {

        $conn->rollback();
        error_log("Appointment booking error: " . $e->getMessage());

        $_SESSION['updateError'] = "Failed to book appointment. Please try again.";
        header("Location: " . BASE_URL . "/Admin/pages/patients.php");
        exit();
    }
}

$conn->close();
?>
