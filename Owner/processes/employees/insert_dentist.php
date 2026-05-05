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

    $lastName       = trim($_POST['lastName']);
    $firstName      = trim($_POST['firstName']);
    $middleName     = trim($_POST['middleName']);
    $gender         = $_POST['gender'];
    $dateofBirth    = $_POST['dateofBirth'] ?? '';
    $email          = trim($_POST['email']);
    $contactNumber  = trim($_POST['contactNumber']);
    $licenseNumber  = trim($_POST['licenseNumber']);
    $status         = $_POST['status'];
    $dateStarted    = $_POST['dateStarted'] ?? null;
    $branches       = $_POST['branches'] ?? [];
    $services       = $_POST['services'] ?? [];
    $schedule       = $_POST['schedule'] ?? [];

    if (!empty($email) && !isValidEmailDomain($email)) {
        $_SESSION['updateError'] = "Email domain is not valid or unreachable.";
        header("Location: " . BASE_URL . "/Owner/pages/employees.php?tab=dentist");
        exit();
    }

    $safeLast = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "_", $lastName));

    $tempSignature = null;
    $tempProfile = null;

    if (isset($_FILES['signatureImage']) && $_FILES['signatureImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/images/dentists/signature/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['signatureImage']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $tempSignature = "tmp_sig_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['signatureImage']['tmp_name'], $uploadDir . $tempSignature);
        } else {
            $_SESSION['updateError'] = "Invalid file type for signature. Only JPG, PNG, or WEBP allowed.";
            header("Location: " . BASE_URL . "/Owner/pages/employees.php?tab=dentist");
            exit();
        }
    }

    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/images/dentists/profile/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $tempProfile = "tmp_prof_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadDir . $tempProfile);
        } else {
            $_SESSION['updateError'] = "Invalid file type. Only JPG, PNG, or WEBP allowed.";
            header("Location: " . BASE_URL . "/Owner/pages/employees.php?tab=dentist");
            exit();
        }
    }

    try {

        [$dob_enc, $dob_iv, $dob_tag] = encryptField($dateofBirth, $ENCRYPTION_KEY);
        [$contact_enc, $contact_iv, $contact_tag] = encryptField($contactNumber, $ENCRYPTION_KEY);
        [$license_enc, $license_iv, $license_tag] = encryptField($licenseNumber, $ENCRYPTION_KEY);

        $stmt = $conn->prepare("
            INSERT INTO dentist (
                last_name, first_name, middle_name, gender,
                date_of_birth, date_of_birth_iv, date_of_birth_tag,
                email,
                contact_number, contact_number_iv, contact_number_tag,
                license_number, license_number_iv, license_number_tag,
                date_started, status, date_updated, signature_image, profile_image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
        ");

        $null = null;

        $stmt->bind_param(
            "ssssssssssssssssss",
            $lastName, $firstName, $middleName, $gender,
            $dob_enc, $dob_iv, $dob_tag,
            $email,
            $contact_enc, $contact_iv, $contact_tag,
            $license_enc, $license_iv, $license_tag,
            $dateStarted, $status,
            $null, $null
        );

        $stmt->execute();
        $dentistId = $stmt->insert_id;
        $stmt->close();

        $finalSig = null;
        $finalProf = null;

        if ($tempSignature) {
            $ext = pathinfo($tempSignature, PATHINFO_EXTENSION);
            $finalSig = "{$dentistId}_{$safeLast}_signature." . $ext;
            rename(
                $_SERVER['DOCUMENT_ROOT'] . "/Smile-ify/images/dentists/signature/" . $tempSignature,
                $_SERVER['DOCUMENT_ROOT'] . "/Smile-ify/images/dentists/signature/" . $finalSig
            );
        }

        if ($tempProfile) {
            $ext = pathinfo($tempProfile, PATHINFO_EXTENSION);
            $finalProf = "{$dentistId}_{$safeLast}_profile." . $ext;
            rename(
                $_SERVER['DOCUMENT_ROOT'] . "/Smile-ify/images/dentists/profile/" . $tempProfile,
                $_SERVER['DOCUMENT_ROOT'] . "/Smile-ify/images/dentists/profile/" . $finalProf
            );
        }

        $upd = $conn->prepare("UPDATE dentist SET signature_image=?, profile_image=? WHERE dentist_id=?");
        $upd->bind_param("ssi", $finalSig, $finalProf, $dentistId);
        $upd->execute();
        $upd->close();

        if (!empty($branches)) {
            $stmt2 = $conn->prepare("INSERT INTO dentist_branch (dentist_id, branch_id) VALUES (?, ?)");
            foreach ($branches as $branchId) {
                $stmt2->bind_param("ii", $dentistId, $branchId);
                $stmt2->execute();
            }
            $stmt2->close();
        }

        if (!empty($services)) {
            $stmt3 = $conn->prepare("INSERT INTO dentist_service (dentist_id, service_id) VALUES (?, ?)");
            foreach ($services as $serviceId) {
                $stmt3->bind_param("ii", $dentistId, $serviceId);
                $stmt3->execute();
            }
            $stmt3->close();
        }

        if (!empty($schedule)) {

            $stmt4 = $conn->prepare("
                INSERT INTO dentist_schedule (dentist_id, day, branch_id, start_time, end_time)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($schedule as $day => $entries) {

                $branchesArr = $entries["branch"] ?? [];
                $startArr    = $entries["start"] ?? [];
                $endArr      = $entries["end"] ?? [];

                for ($i = 0; $i < count($branchesArr); $i++) {

                    $branch_id = !empty($branchesArr[$i]) ? (int)$branchesArr[$i] : null;
                    if ($branch_id === null) continue;

                    $rawStart = $startArr[$i] ?? "";
                    $rawEnd   = $endArr[$i] ?? "";

                    $isWholeDay = (
                        ($rawStart === "" || $rawStart === null) &&
                        ($rawEnd === "" || $rawEnd === null)
                    );

                    if ($isWholeDay) {
                        $start_time = "09:00";
                        $end_time   = "16:30";
                    } else {
                        $start_time = $rawStart ?: null;
                        $end_time   = $rawEnd ?: null;
                    }

                    $stmt4->bind_param(
                        "isiss",
                        $dentistId,
                        $day,
                        $branch_id,
                        $start_time,
                        $end_time
                    );

                    $stmt4->execute();
                }
            }

            $stmt4->close();
        }

        $_SESSION['updateSuccess'] = "Dentist added successfully!";
        

    } catch (Exception $e) {
        $_SESSION['updateError'] = "Error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/Owner/pages/employees.php?tab=dentist");
    exit();
}

header("Location: " . BASE_URL . "/Owner/pages/employees.php?tab=dentist");
exit();
?>
