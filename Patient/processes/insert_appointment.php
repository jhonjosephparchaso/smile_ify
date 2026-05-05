<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $bookingType = $_POST['bookingType'] ?? "self";

    $appointmentBranch   = $_POST['appointmentBranch'];
    $appointmentServices = $_POST['appointmentServices'] ?? [];
    $quantities          = $_POST['serviceQuantity'] ?? [];
    $appointmentDentist  = $_POST['appointmentDentist'];
    $appointmentDate     = $_POST['appointmentDate'];
    $appointmentTime     = $_POST['appointmentTime'];
    $notes               = $_POST['notes'] ?? '';
    $relationship        = $_POST['relationship'] ?? null;

    if (empty($appointmentServices)) {
        $_SESSION['updateError'] = "Please select at least one service.";
        header("Location: " . BASE_URL . "/Patient/index.php");
        exit;
    }

    if ($appointmentDentist === "none" || empty($appointmentDentist)) {
        $appointmentDentist = null;
    }

    $user_id     = $_SESSION['user_id'];
    $guardian_id = $_SESSION['user_id'];

    try {
        $conn->begin_transaction();

        if ($bookingType === "child") {

            $childLast   = trim($_POST['childLastName']);
            $childFirst  = trim($_POST['childFirstName']);
            $childDob    = trim($_POST['childDob']);
            $childGender = trim($_POST['childGender']);

            if (empty($childFirst) || empty($childLast) || empty($childDob) || empty($childGender)) {
                throw new Exception("Please fill in all dependent details.");
            }

            [$cdob_enc, $cdob_iv, $cdob_tag] = encryptField($childDob, $ENCRYPTION_KEY);

            $insertChild = $conn->prepare("
                INSERT INTO users
                (username, password, last_name, first_name, gender,
                    date_of_birth, date_of_birth_iv, date_of_birth_tag,
                    email, contact_number, contact_number_iv, contact_number_tag,
                    role, branch_id, guardian_id, relationship)
                VALUES (NULL, NULL, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, 'patient', ?, ?, ?)
            ");

            $insertChild->bind_param(
                "ssssssiis",
                $childLast,
                $childFirst,
                $childGender,
                $cdob_enc,
                $cdob_iv,
                $cdob_tag,
                $appointmentBranch,
                $guardian_id,
                $relationship
            );

            if (!$insertChild->execute()) {
                throw new Exception("Failed to create dependent.");
            }

            $user_id = $insertChild->insert_id;
            $insertChild->close();
        }

        if ($bookingType === "existing") {

            $existingId = intval($_POST['existingDependentId']);

            if ($existingId <= 0) {
                throw new Exception("Please select a dependent.");
            }

            $check = $conn->prepare("
                SELECT user_id 
                FROM users 
                WHERE user_id = ? AND guardian_id = ? AND username IS NULL
            ");
            $check->bind_param("ii", $existingId, $guardian_id);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows === 0) {
                $check->close();
                throw new Exception("Invalid dependent selected.");
            }

            $check->close();
            $user_id = $existingId;
        }

        $appointment_sql = "
            INSERT INTO appointment_transaction 
                (user_id, branch_id, dentist_id, appointment_date, appointment_time, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Booked')
        ";

        $appointment_stmt = $conn->prepare($appointment_sql);
        $appointment_stmt->bind_param(
            "iiisss",
            $user_id,
            $appointmentBranch,
            $appointmentDentist,
            $appointmentDate,
            $appointmentTime,
            $notes
        );

        if (!$appointment_stmt->execute()) {
            throw new Exception("Failed to book appointment.");
        }

        $appointment_transaction_id = $appointment_stmt->insert_id;
        $appointment_stmt->close();

        $service_sql = "
            INSERT INTO appointment_services 
                (appointment_transaction_id, service_id, quantity)
            VALUES (?, ?, ?)
        ";

        $service_stmt = $conn->prepare($service_sql);

        foreach ($appointmentServices as $service_id) {
            $service_id = (int)$service_id;
            $qty = isset($quantities[$service_id]) && (int)$quantities[$service_id] > 0
                ? (int)$quantities[$service_id]
                : 1;

            $service_stmt->bind_param(
                "iii",
                $appointment_transaction_id,
                $service_id,
                $qty
            );

            if (!$service_stmt->execute()) {
                $service_stmt->close();
                throw new Exception("Failed to add services.");
            }
        }

        $service_stmt->close();

        $user_update_sql = "UPDATE users SET date_updated = NOW() WHERE user_id = ?";
        $user_update_stmt = $conn->prepare($user_update_sql);
        $user_update_stmt->bind_param("i", $user_id);
        $user_update_stmt->execute();
        $user_update_stmt->close();

        $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_message = "Your appointment on $appointmentDate at $appointmentTime was successfully booked!";
        $notif_stmt->bind_param("is", $user_id, $notif_message);
        $notif_stmt->execute();
        $notif_stmt->close();

        if ($bookingType === "child" || $bookingType === "existing") {

            $guardianId = $_SESSION['user_id'];

            if ($guardianId !== $user_id) {

                $guardianMsg = "Your dependent's appointment on $appointmentDate at $appointmentTime was successfully booked.";

                $guardianNotif = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $guardianNotif->bind_param("is", $guardianId, $guardianMsg);
                $guardianNotif->execute();
                $guardianNotif->close();
            }
        }

        $conn->commit();

        $_SESSION['updateSuccess'] = "Appointment booked successfully.";
        header("Location: " . BASE_URL . "/Patient/pages/calendar.php");
        exit;

    } catch (Exception $e) {

        $conn->rollback();
        error_log("Error booking appointment: " . $e->getMessage());
        $_SESSION['updateError'] = $e->getMessage();
        header("Location: " . BASE_URL . "/Patient/index.php");
        exit;
    }

} else {
    header("Location: " . BASE_URL . "/Patient/index.php");
    exit;
}

$conn->close();
?>
