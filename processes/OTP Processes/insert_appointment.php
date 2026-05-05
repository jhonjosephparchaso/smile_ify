<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["verify"])) {
    $otp = $_SESSION['otp'] ?? null;
    $otp_code = trim($_POST['otpCode']);
    $otp_created = $_SESSION['otp_created'] ?? 0;
    $expiry_limit = 60;
    $email = $_SESSION['mail'] ?? null;

    if (time() - $otp_created > $expiry_limit) {
        $_SESSION['otp_error'] = "OTP has expired. Please request a new one.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/otp_verification.php");
        exit;
    }

    if ((string)$otp !== (string)$otp_code) {
        $_SESSION['otp_error'] = "Invalid OTP code.";
        header("Location: " . BASE_URL . "/includes/OTP Includes/otp_verification.php");
        exit;
    }

    if (isset($_SESSION['verified_data'])) {

        $bookingType = $_SESSION['verified_data']['bookingType'];

        $lastName       = $_SESSION['verified_data']['lastName'];
        $firstName      = $_SESSION['verified_data']['firstName'];
        $middleName     = $_SESSION['verified_data']['middleName'];
        $email          = $_SESSION['verified_data']['email'];
        $gender         = $_SESSION['verified_data']['gender'];
        $dateofBirth    = $_SESSION['verified_data']['dateofBirth'];
        $contactNumber  = $_SESSION['verified_data']['contactNumber'];

        $childFirstName = $_SESSION['verified_data']['childFirstName'] ?? null;
        $childLastName  = $_SESSION['verified_data']['childLastName'] ?? null;
        $childGender    = $_SESSION['verified_data']['childGender'] ?? null;
        $childDob       = $_SESSION['verified_data']['childDob'] ?? null;
        $relationship   = $_SESSION['verified_data']['relationship'] ?? null;

        $appointmentBranch = $_SESSION['verified_data']['appointmentBranch'];
        $appointmentServices = $_SESSION['verified_data']['appointmentServices'];
        $appointmentDentist = $_SESSION['verified_data']['appointmentDentist'];
        $appointmentDate = $_SESSION['verified_data']['appointmentDate'];
        $appointmentTime = $_SESSION['verified_data']['appointmentTime'];
        $notes = $_SESSION['verified_data']['notes'];

        try {
            $conn->begin_transaction();

            if ($bookingType === "self") {

                $username = generateUniqueUsername($lastName, $firstName, $conn);
                $default_password = generatePassword($lastName);
                $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

                [$dob_enc, $dob_iv, $dob_tag] = encryptField($dateofBirth, $ENCRYPTION_KEY);
                [$contact_enc, $contact_iv, $contact_tag] = encryptField($contactNumber, $ENCRYPTION_KEY);

                $user_sql = "INSERT INTO users 
                    (username, password, last_name, middle_name, first_name, gender,
                    date_of_birth, date_of_birth_iv, date_of_birth_tag,
                    email,
                    contact_number, contact_number_iv, contact_number_tag,
                    role, branch_id, guardian_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'patient', ?, NULL)";

                $stmt = $conn->prepare($user_sql);
                $stmt->bind_param(
                    "sssssssssssssi",
                    $username,
                    $hashed_password,
                    $lastName,
                    $middleName,
                    $firstName,
                    $gender,
                    $dob_enc,
                    $dob_iv,
                    $dob_tag,
                    $email,
                    $contact_enc,
                    $contact_iv,
                    $contact_tag,
                    $appointmentBranch
                );
                $stmt->execute();
                $user_id = $stmt->insert_id;

            }

            else if ($bookingType === "child") {

                $username = generateUniqueUsername($lastName, $firstName, $conn);
                $default_password = generatePassword($lastName);
                $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

                [$dob_enc, $dob_iv, $dob_tag] = encryptField($dateofBirth, $ENCRYPTION_KEY);
                [$contact_enc, $contact_iv, $contact_tag] = encryptField($contactNumber, $ENCRYPTION_KEY);

                $g_sql = "INSERT INTO users 
                    (username, password, last_name, middle_name, first_name, gender,
                    date_of_birth, date_of_birth_iv, date_of_birth_tag,
                    email, contact_number, contact_number_iv, contact_number_tag,
                    role, branch_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'patient', ?)";

                $g_stmt = $conn->prepare($g_sql);
                $g_stmt->bind_param(
                    "sssssssssssssi",
                    $username,
                    $hashed_password,
                    $lastName,
                    $middleName,
                    $firstName,
                    $gender,
                    $dob_enc,
                    $dob_iv,
                    $dob_tag,
                    $email,
                    $contact_enc,
                    $contact_iv,
                    $contact_tag,
                    $appointmentBranch
                );
                $g_stmt->execute();
                $guardian_id = $g_stmt->insert_id;

                [$cdob_enc, $cdob_iv, $cdob_tag] = encryptField($childDob, $ENCRYPTION_KEY);

                $c_sql = "INSERT INTO users
                                    (username, password, last_name, first_name, gender,
                                    date_of_birth, date_of_birth_iv, date_of_birth_tag,
                                    email, contact_number, contact_number_iv, contact_number_tag,
                                    role, branch_id, guardian_id, relationship)
                                VALUES
                                    (NULL, NULL, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, 'patient', ?, ?, ?)";

                $c_stmt = $conn->prepare($c_sql);
                $c_stmt->bind_param(
                    "ssssssiis",
                    $childLastName,
                    $childFirstName,
                    $childGender,
                    $cdob_enc,
                    $cdob_iv,
                    $cdob_tag,
                    $appointmentBranch,
                    $guardian_id,
                    $relationship
                );
                $c_stmt->execute();
                $user_id = $c_stmt->insert_id;
            }

            if ($appointmentDentist === "none") $appointmentDentist = null;

            $appointment_sql = "INSERT INTO appointment_transaction 
                (user_id, branch_id, dentist_id, appointment_date, appointment_time, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, 'Booked')";

            $appointment_stmt = $conn->prepare($appointment_sql);
            $appointment_stmt->bind_param("iiisss", 
                $user_id, 
                $appointmentBranch, 
                $appointmentDentist, 
                $appointmentDate, 
                $appointmentTime, 
                $notes
            );
            $appointment_stmt->execute();

            $appointment_transaction_id = $appointment_stmt->insert_id;
            if (!empty($appointmentServices)) {
                $service_sql = "INSERT INTO appointment_services 
                    (appointment_transaction_id, service_id, quantity)
                    VALUES (?, ?, ?)";
                $service_stmt = $conn->prepare($service_sql);

                foreach ($appointmentServices as $service_id) {
                    $qty = 1;
                    $service_stmt->bind_param("iii", $appointment_transaction_id, $service_id, $qty);
                    $service_stmt->execute();
                }
            }
            $conn->commit();

            $welcomeMsg = "Welcome to Smile-ify! Your account has been created.";
            $childMsg   = "Your appointment on $appointmentDate at $appointmentTime was successfully booked.";
            $guardianMsg = "Your dependent's appointment on $appointmentDate at $appointmentTime was successfully booked.";

            if ($bookingType === "self") {

                $notif1 = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notif1->bind_param("is", $user_id, $welcomeMsg);
                $notif1->execute();
                $notif1->close();

                $notif2 = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notif2->bind_param("is", $user_id, $childMsg);
                $notif2->execute();
                $notif2->close();
            }

            if ($bookingType === "child") {

                $notifChild = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notifChild->bind_param("is", $user_id, $childMsg);
                $notifChild->execute();
                $notifChild->close();

                $notifGuardian = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notifGuardian->bind_param("is", $guardian_id, $guardianMsg);
                $notifGuardian->execute();
                $notifGuardian->close();

                $notifWelcome = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notifWelcome->bind_param("is", $guardian_id, $welcomeMsg);
                $notifWelcome->execute();
                $notifWelcome->close();
            }

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaction failed: " . $e->getMessage());
            $_SESSION['otp_error'] = "Something went wrong during account creation. Please try again.";
            header("Location: " . BASE_URL . "/includes/OTP Includes/otp_verification.php");
            exit;
        }

        $servicesHtml = "";
        $totalPrice = 0;
        $totalDuration = 0;

        if (!empty($appointmentServices)) {
            $placeholders = implode(',', array_fill(0, count($appointmentServices), '?'));
            $types = str_repeat('i', count($appointmentServices));

            $stmt = $conn->prepare("SELECT service_id, name, price, duration_minutes 
                FROM service WHERE service_id IN ($placeholders)");
            $stmt->bind_param($types, ...$appointmentServices);
            $stmt->execute();
            $result = $stmt->get_result();

            $servicesHtml .= "<ul>";
            while ($row = $result->fetch_assoc()) {
                $linePrice = $row['price'];
                $lineDuration = $row['duration_minutes'];

                $servicesHtml .= "<li>{$row['name']} – ₱" . number_format($linePrice, 2) .
                                " ({$lineDuration} mins)</li>";

                $totalPrice += $linePrice;
                $totalDuration += $lineDuration;
            }
            $servicesHtml .= "</ul>";
            $stmt->close();
        }

        $totalFormatted = number_format($totalPrice, 2);

        $appointmentDateTime = new DateTime("$appointmentDate $appointmentTime");
        $appointmentDateTime->modify("+{$totalDuration} minutes");
        $formattedEndTime = $appointmentDateTime->format('h:i A');

        $branch_sql = "SELECT address FROM branch WHERE branch_id = ?";
        $branch_stmt = $conn->prepare($branch_sql);
        $branch_stmt->bind_param("i", $appointmentBranch);
        $branch_stmt->execute();
        $branch_result = $branch_stmt->get_result();
        $branch_row = $branch_result->fetch_assoc();
        $branchAddress = $branch_row['address'] ?? 'N/A';
        $branch_stmt->close();

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

        $childSection = "";
        if ($bookingType === "child") {
            $childSection = "
                <hr>
                <p><strong>Dependent Information:</strong></p>
                <p>
                    <strong>Name:</strong> {$childFirstName} {$childLastName}<br>
                    <strong>Gender:</strong> " . ucfirst($childGender) . "<br>
                    <strong>Date of Birth:</strong> {$childDob}
                </p>
            ";
        }

        $mail->isHTML(true);
        $mail->Subject = "Smile-ify Login Credentials and Appointment Details";
        $mail->Body = "
            <p>Dear <strong>$username</strong>,</p>
            <p>Your Smile-ify account has been successfully verified.</p>
            <p>You may now log in using the following credentials:</p>
            <p>
                <strong>Username:</strong> $username<br>
                <strong>Password:</strong> $default_password
            </p>

            <p style='color:#c0392b; font-weight:bold;'>
                NOTE: Kindly change your password after your first login to ensure account security.
            </p>
            <hr>
            <p><strong>Appointment Details:</strong></p>
            <p>
                <strong>Appointment ID:</strong> $appointment_transaction_id<br>
                <strong>Date:</strong> $appointmentDate<br>
                <strong>Time:</strong> $appointmentTime<br>
                <strong>Estimated End Time:</strong> $formattedEndTime<br>
                <strong>Location:</strong> $branchAddress
            </p>

            $childSection

            <p><strong>Selected Services:</strong></p>
            $servicesHtml
            <p><strong>Total:</strong> ₱{$totalFormatted} ({$totalDuration} mins total)</p>
            <br>
            <p><i>Smile with confidence.</i></p>
            <p>Best regards,<br><strong>Smile-ify</strong></p>
        ";

        try {
            $mail->send();

            $_SESSION['login_success'] = "Email has been sent with your login credentials.";
            header("Location: " . BASE_URL . "/index.php");
            exit;

        } catch (Exception $e) {
            error_log("PHPMailer Exception: " . $e->getMessage());
            $_SESSION['otp_error'] = "Failed to send email. Please try again.";
            header("Location: " . BASE_URL . "/includes/OTP Includes/otp_verification.php");
            exit;
        }
    }

    $conn->close();
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

function generatePassword($lastName) {
    $cleanLastName = preg_replace("/[^a-zA-Z]/", "", $lastName);
    $prefix = strtolower($cleanLastName);
    $number = rand(1000, 9999);
    $specials = ['!', '@', '#', '$', '%'];
    $symbol = $specials[array_rand($specials)];
    return $prefix . $number . $symbol;
}
?>
