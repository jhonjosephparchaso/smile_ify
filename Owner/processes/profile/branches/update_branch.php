<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
        $_SESSION['updateError'] = "Unauthorized access.";
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }

    $branch_id     = intval($_POST["branch_id"] ?? 0);
    $branchName    = trim($_POST["branchName"] ?? "");
    $nickname      = trim($_POST["nickname"] ?? "");
    $address       = trim($_POST["address"] ?? "");
    $phone_number  = trim($_POST["contactNumber"] ?? "");
    $map_url       = trim($_POST["map_url"] ?? "");
    $status        = $_POST["status"] ?? "Active";
    $dental_chairs = intval($_POST["chairCount"] ?? 1);

    try {
        $check_sql = "SELECT name, nickname, address, phone_number, dental_chairs, status, map_url
                    FROM branch
                    WHERE branch_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $branch_id);
        $check_stmt->execute();
        $current = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if (!$current) {
            $_SESSION['updateError'] = "Branch not found.";
        } else {
            if (
                $current['name'] !== $branchName ||
                $current['nickname'] !== $nickname ||
                $current['address'] !== $address ||
                $current['phone_number'] !== $phone_number ||
                (int)$current['dental_chairs'] !== $dental_chairs ||
                $current['status'] !== $status ||
                $current['map_url'] !== $map_url
            ) {
                $sql = "UPDATE branch 
                        SET name = ?, 
                            nickname = ?, 
                            address = ?, 
                            phone_number = ?, 
                            dental_chairs = ?,
                            status = ?, 
                            map_url = ?,
                            date_updated = NOW()
                        WHERE branch_id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param(
                    "ssssissi",
                    $branchName,
                    $nickname,
                    $address,
                    $phone_number,
                    $dental_chairs,
                    $status,
                    $map_url,
                    $branch_id
                );

                if ($stmt->execute() && $stmt->affected_rows > 0) {

                    if ($current['status'] === 'Active' && $status === 'Inactive') {

                        if (($_POST['confirmDeactivate'] ?? '0') !== '1') {
                            $_SESSION['updateError'] = "Branch deactivation must be confirmed.";
                            header("Location: " . BASE_URL . "/Owner/pages/profile.php");
                            exit;
                        }

                        $apptSql = "
                            SELECT 
                                at.appointment_transaction_id,
                                at.user_id,
                                u.guardian_id
                            FROM appointment_transaction at
                            JOIN users u ON u.user_id = at.user_id
                            WHERE at.branch_id = ?
                            AND at.status = 'Booked'
                            AND at.appointment_date >= CURDATE()
                        ";

                        $apptStmt = $conn->prepare($apptSql);
                        $apptStmt->bind_param("i", $branch_id);
                        $apptStmt->execute();
                        $appointments = $apptStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        $apptStmt->close();

                        if (!empty($appointments)) {

                            $updateAppt = $conn->prepare("
                                UPDATE appointment_transaction
                                SET status = 'Pending Reschedule'
                                WHERE appointment_transaction_id = ?
                            ");

                            $notif = $conn->prepare("
                                INSERT INTO notifications (user_id, appointment_transaction_id, message)
                                VALUES (?, ?, ?)
                            ");

                            foreach ($appointments as $appt) {

                                $appointmentTransactionId = (int)$appt['appointment_transaction_id'];

                                $updateAppt->bind_param("i", $appointmentTransactionId);
                                $updateAppt->execute();

                                $notifyUserId = !empty($appt['guardian_id'])
                                    ? (int)$appt['guardian_id']
                                    : (int)$appt['user_id'];

                                $message = "Your dental appointment requires action because the branch has been deactivated.
                                            Please confirm a new schedule or cancel the appointment.";

                                $notif->bind_param(
                                    "iis",
                                    $notifyUserId,
                                    $appointmentTransactionId,
                                    $message
                                );

                                $notif->execute();
                            }

                            $updateAppt->close();
                            $notif->close();
                        }
                    }

                    $_SESSION['updateSuccess'] = "Branch updated successfully!";
                }

                $stmt->close();
            }
        }
    } catch (Exception $e) {
        $_SESSION['updateError'] = "Database error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/Owner/pages/profile.php");
    exit;
} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$conn->close();
