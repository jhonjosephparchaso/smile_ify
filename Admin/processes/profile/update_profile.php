<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['updateError'] = "Unauthorized access.";
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $address = trim($_POST['address'] ?? '');
    $contact_number = trim($_POST['contactNumber'] ?? '');

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("
            SELECT contact_number, contact_number_iv, contact_number_tag,
                    address, address_iv, address_tag
            FROM users
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc() ?: [];

        $existing_contact = !empty($existing['contact_number'])
            ? decryptField($existing['contact_number'], $existing['contact_number_iv'], $existing['contact_number_tag'])
            : null;
        $existing_address = !empty($existing['address'])
            ? decryptField($existing['address'], $existing['address_iv'], $existing['address_tag'])
            : null;

        $hasChanges = false;

        if ($contact_number !== '' && $contact_number !== $existing_contact) {
            [$contact_enc, $contact_iv, $contact_tag] = encryptField($contact_number);
            $hasChanges = true;
        } else {
            $contact_enc = $existing['contact_number'];
            $contact_iv  = $existing['contact_number_iv'];
            $contact_tag = $existing['contact_number_tag'];
        }

        if ($address !== '' && $address !== $existing_address) {
            [$address_enc, $address_iv, $address_tag] = encryptField($address);
            $hasChanges = true;
        } else {
            $address_enc = $existing['address'];
            $address_iv  = $existing['address_iv'];
            $address_tag = $existing['address_tag'];
        }

        if ($hasChanges) {
            $sql = "UPDATE users SET 
                        contact_number = ?, 
                        contact_number_iv = ?, 
                        contact_number_tag = ?, 
                        address = ?, 
                        address_iv = ?, 
                        address_tag = ?, 
                        date_updated = NOW()
                    WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssi",
                $contact_enc, $contact_iv, $contact_tag,
                $address_enc, $address_iv, $address_tag,
                $user_id
            );
            $stmt->execute();
            $conn->commit();

            $_SESSION['updateSuccess'] = "Profile updated successfully.";
        }
        header("Location: " . BASE_URL . "/Admin/pages/profile.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Secretary profile update failed: " . $e->getMessage());
        $_SESSION['updateError'] = "Database update failed.";
        header("Location: " . BASE_URL . "/Admin/pages/profile.php");
        exit();
    }
}
?>
