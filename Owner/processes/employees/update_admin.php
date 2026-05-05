<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

function isValidEmailDomain($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, "MX");
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id        = $_POST['user_id'] ?? null;
    $lastName       = trim($_POST['lastName'] ?? '');
    $firstName      = trim($_POST['firstName'] ?? '');
    $middleName     = trim($_POST['middleName'] ?? '');
    $gender         = $_POST['gender'] ?? '';
    $dateofBirth    = $_POST['dateofBirth'] ?? '';
    $email          = trim($_POST['email'] ?? '');
    $contactNumber  = trim($_POST['contactNumber'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $branch_id      = $_POST['branchAssignment'] ?? null;
    $status         = $_POST['status'] ?? 'Inactive';
    $dateStarted    = $_POST['dateStarted'] ?? null;

    if (!empty($email) && !isValidEmailDomain($email)) {
        $_SESSION['updateError'] = "Email domain is not valid or unreachable.";
        header("Location: " . BASE_URL . "/Owner/pages/employees.php");
        exit();
    }

    try {
        $checkSql = "SELECT last_name, first_name, middle_name, gender, 
                            date_of_birth, date_of_birth_iv, date_of_birth_tag,
                            email, 
                            contact_number, contact_number_iv, contact_number_tag,
                            address, address_iv, address_tag,
                            branch_id, status, date_started 
                        FROM users WHERE user_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $user_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $current = $result->fetch_assoc();
        $checkStmt->close();

        if (!$current) {
            $_SESSION['updateError'] = "User not found.";
            header("Location: " . BASE_URL . "/Owner/pages/employees.php");
            exit();
        }

        $current_dob = decryptField($current['date_of_birth'], $current['date_of_birth_iv'], $current['date_of_birth_tag'], $ENCRYPTION_KEY);
        $current_contact = decryptField($current['contact_number'], $current['contact_number_iv'], $current['contact_number_tag'], $ENCRYPTION_KEY);
        $current_address = decryptField($current['address'], $current['address_iv'], $current['address_tag'], $ENCRYPTION_KEY);

        $hasChanges = (
            $current['last_name'] !== $lastName ||
            $current['first_name'] !== $firstName ||
            $current['middle_name'] !== $middleName ||
            $current['gender'] !== $gender ||
            $current_dob !== $dateofBirth ||
            $current['email'] !== $email ||
            $current_contact !== $contactNumber ||
            $current_address !== $address ||
            $current['branch_id'] != $branch_id ||
            $current['status'] !== $status ||
            $current['date_started'] !== $dateStarted
        );

        if ($hasChanges) {
            [$dob_enc, $dob_iv, $dob_tag] = encryptField($dateofBirth, $ENCRYPTION_KEY);
            [$contact_enc, $contact_iv, $contact_tag] = encryptField($contactNumber, $ENCRYPTION_KEY);
            [$address_enc, $address_iv, $address_tag] = encryptField($address, $ENCRYPTION_KEY);

            $sql = "UPDATE users 
                    SET last_name = ?, 
                        first_name = ?, 
                        middle_name = ?, 
                        gender = ?, 
                        date_of_birth = ?, 
                        date_of_birth_iv = ?, 
                        date_of_birth_tag = ?, 
                        email = ?, 
                        contact_number = ?, 
                        contact_number_iv = ?, 
                        contact_number_tag = ?, 
                        address = ?, 
                        address_iv = ?, 
                        address_tag = ?, 
                        branch_id = ?, 
                        status = ?, 
                        date_started = ?, 
                        date_updated = NOW()
                    WHERE user_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssssssssissi",
                $lastName, $firstName, $middleName, $gender,
                $dob_enc, $dob_iv, $dob_tag,
                $email,
                $contact_enc, $contact_iv, $contact_tag,
                $address_enc, $address_iv, $address_tag,
                $branch_id, $status, $dateStarted,
                $user_id
            );

            if ($stmt->execute()) {
                $_SESSION['updateSuccess'] = "Secretary updated successfully!";
                $forceStmt = $conn->prepare("UPDATE users SET force_logout = 1 WHERE user_id = ?");
                $forceStmt->bind_param("i", $user_id);
                $forceStmt->execute();
                $forceStmt->close();
            } else {
                $_SESSION['updateError'] = "Failed to update Secretary.";
            }

            $stmt->close();
        }

    } catch (Exception $e) {
        $_SESSION['updateError'] = "Error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/Owner/pages/employees.php");
    exit();
} else {
    header("Location: " . BASE_URL . "/Owner/pages/employees.php");
    exit();
}
?>
