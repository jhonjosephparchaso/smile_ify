<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    if (!isValidEmailDomain($email)) {
        $_SESSION['updateError'] = "Email domain is not valid or unreachable.";
        header("Location: " . BASE_URL . "/Owner/pages/employees.php");
        exit();
    }

    try {
        $branch_name = '';
        if ($branch_id) {
            $stmtBranch = $conn->prepare("SELECT name FROM branch WHERE branch_id = ?");
            $stmtBranch->bind_param("i", $branch_id);
            $stmtBranch->execute();
            $stmtBranch->bind_result($branch_name);
            $stmtBranch->fetch();
            $stmtBranch->close();
        }

        $username = generateUniqueUsername($lastName, $firstName, $conn);
        $raw_password = generatePasswordFromLastName($lastName);
        $password = password_hash($raw_password, PASSWORD_DEFAULT);

        [$dob_enc, $dob_iv, $dob_tag]         = encryptField($dateofBirth, $ENCRYPTION_KEY);
        [$contact_enc, $contact_iv, $contact_tag] = encryptField($contactNumber, $ENCRYPTION_KEY);
        [$address_enc, $address_iv, $address_tag] = encryptField($address, $ENCRYPTION_KEY);

        $stmt = $conn->prepare("
            INSERT INTO users (
                username, last_name, first_name, middle_name, gender,
                date_of_birth, date_of_birth_iv, date_of_birth_tag,
                email,
                contact_number, contact_number_iv, contact_number_tag,
                address, address_iv, address_tag,
                branch_id, role, status, date_updated, password, date_started
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'admin', ?, NOW(), ?, ?)
        ");

        $stmt->bind_param(
            "sssssssssssssssisss",
            $username,
            $lastName,
            $firstName,
            $middleName,
            $gender,
            $dob_enc,
            $dob_iv,
            $dob_tag,
            $email,
            $contact_enc,
            $contact_iv,
            $contact_tag,
            $address_enc,
            $address_iv,
            $address_tag,
            $branch_id,
            $status,
            $password,
            $dateStarted
        );

        if ($stmt->execute()) {
            $new_admin_id = $stmt->insert_id;

            $notif_msg = "Your Secretary account has been created. Branch Assignment: $branch_name. Username: $username";
            $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $notif_stmt = $conn->prepare($notif_sql);
            $notif_stmt->bind_param("is", $new_admin_id, $notif_msg);
            $notif_stmt->execute();
            $notif_stmt->close();

            require BASE_PATH . '/Mail/phpmailer/PHPMailerAutoload.php';

            $mail = new PHPMailer;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->Port       = SMTP_PORT;
            $mail->SMTPAuth   = SMTP_AUTH;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;

            $mail->setFrom('smileify.web@gmail.com', 'Smile-ify Team');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Your Smile-ify Secretary Account";

            if (!empty($dateStarted)) {
                $ts = strtotime($dateStarted);
                $dateStartedFormatted = $ts ? date('F j, Y', $ts) : $dateStarted;
            } else {
                $dateStartedFormatted = 'Not provided';
            }

            $mail->Body = "
                <p>Dear <strong>{$firstName} {$lastName}</strong>,</p>
                <p>Your Secretary account for <strong>Smile-ify</strong> has been successfully created.</p>

                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Date to Start:</strong> {$dateStartedFormatted}</p>

                <p><strong>Branch Assignment:</strong> {$branch_name}</p>

                <p><strong>Login Credentials:</strong></p>
                <p>
                    <strong>Username:</strong> {$username}<br>
                    <strong>Password:</strong> {$raw_password}
                </p>

                <p style='color:#c0392b; font-weight:bold;'>
                    NOTE: Kindly change your password after your first login to ensure account security.
                </p>

                <p>You may now access the Secretary dashboard.</p>

                <br>
                <p>Best regards,<br><strong>Smile-ify Team</strong></p>
            ";

            try {
                $mail->send();
                $_SESSION['updateSuccess'] = "Secretary added successfully";
            } catch (Exception $e) {
                error_log("Mail Error: " . $mail->ErrorInfo);
                $_SESSION['updateSuccess'] = "Secretary added successfully";
            }

        } else {
            $_SESSION['updateError'] = "Failed to insert Secretary.";
        }

        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['updateError'] = "Error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/Owner/pages/employees.php");
    exit();
} else {
    header("Location: " . BASE_URL . "/Owner/pages/employees.php");
    exit();
}

function generateUniqueUsername($lastName, $firstName, $conn) {
    $username_base = $lastName . '_' . strtoupper(substr($firstName, 0, 1));
    $username = $username_base;
    $counter = 0;

    $check_sql = "SELECT username FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);

    do {
        if ($counter > 0) {
            $username = $username_base . $counter;
        }

        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();
        $counter++;
    } while ($check_stmt->num_rows > 0);

    return $username;
}

function generatePasswordFromLastName($lastName) {
    $cleanLastName = preg_replace("/[^a-zA-Z]/", "", $lastName);
    $prefix = strtolower($cleanLastName);
    $number = rand(1000, 9999);
    $specials = ['!', '@', '#', '$', '%'];
    $symbol = $specials[array_rand($specials)];
    return $prefix . $number . $symbol;
}

function isValidEmailDomain($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, "MX");
}
?>
