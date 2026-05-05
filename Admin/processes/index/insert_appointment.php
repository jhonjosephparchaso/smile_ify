<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

function isValidEmailDomain($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, "MX");
}

function generateUniqueUsername($lastName, $firstName, $conn) {
    $username_base = $lastName . '_' . strtoupper(substr($firstName, 0, 1));
    $username = $username_base;
    $counter = 0;

    $check_sql = "SELECT username FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);

    do {
        if ($counter > 0) $username = $username_base . $counter;
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();
        $counter++;
    } while ($check_stmt->num_rows > 0);

    $check_stmt->close();
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $bookingType = $_POST['bookingType'] ?? 'self';

    $lastName = trim($_POST['lastName']);
    $firstName = trim($_POST['firstName']);
    $middleName = trim($_POST['middleName']);
    $gender = $_POST['gender'];
    $dateofBirth = $_POST['dateofBirth'];
    $email = trim($_POST['email']);
    $contactNumber = trim($_POST['contactNumber']);

    $childLastName = trim($_POST['childLastName'] ?? '');
    $childFirstName = trim($_POST['childFirstName'] ?? '');
    $childGender = $_POST['childGender'] ?? '';
    $childDob = $_POST['childDob'] ?? '';
    $relationship = $_POST['relationship'] ?? null;

    $appointmentBranch = $_POST['appointmentBranch'];
    $appointmentServices = $_POST['appointmentServices'];
    $appointmentDentist = $_POST['appointmentDentist'];
    $appointmentDate = $_POST['appointmentDate'];
    $appointmentTime = $_POST['appointmentTime'];
    $notes = $_POST['notes'];

    if ($appointmentDentist === "none" || empty($appointmentDentist)) $appointmentDentist = null;

    if (!isValidEmailDomain($email)) {
        $_SESSION['updateError'] = "Invalid or unreachable email domain.";
        header("Location: " . BASE_URL . "/Admin/index.php");
        exit();
    }

    $username = null;
    $default_password = null;

    $patient_user_id = null;
    $guardian_id = null;

    try {
        $conn->begin_transaction();

        if ($bookingType === 'self') {

            $username = generateUniqueUsername($lastName, $firstName, $conn);
            $default_password = generatePassword($lastName);
            $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

            [$dob_enc, $dob_iv, $dob_tag] = encryptField($dateofBirth);
            [$contact_enc, $contact_iv, $contact_tag] = encryptField($contactNumber);

            $insert_patient = $conn->prepare("
                INSERT INTO users 
                (username, password, last_name, first_name, middle_name, gender,
                date_of_birth, date_of_birth_iv, date_of_birth_tag,
                email,
                contact_number, contact_number_iv, contact_number_tag,
                role, status, branch_id, guardian_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'patient', 'Active', ?, NULL)
            ");

            $insert_patient->bind_param(
                "sssssssssssssi",
                $username,
                $hashed_password,
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
                $appointmentBranch
            );

            $insert_patient->execute();
            $patient_user_id = $insert_patient->insert_id;
            $guardian_id = $patient_user_id;
            $insert_patient->close();

        } elseif ($bookingType === 'child') {

            if ($childFirstName === '' || $childLastName === '' || $childGender === '' || $childDob === '') {
                throw new Exception("Please fill in all dependent details.");
            }

            $username = generateUniqueUsername($lastName, $firstName, $conn);
            $default_password = generatePassword($lastName);
            $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

            [$dob_enc, $dob_iv, $dob_tag] = encryptField($dateofBirth);
            [$contact_enc, $contact_iv, $contact_tag] = encryptField($contactNumber);

            $g_stmt = $conn->prepare("
                INSERT INTO users 
                (username, password, last_name, first_name, middle_name, gender,
                date_of_birth, date_of_birth_iv, date_of_birth_tag,
                email,
                contact_number, contact_number_iv, contact_number_tag,
                role, status, branch_id, guardian_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'patient', 'Active', ?, NULL)
            ");

            $g_stmt->bind_param(
                "sssssssssssssi",
                $username,
                $hashed_password,
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
                $appointmentBranch
            );
            $g_stmt->execute();
            $guardian_id = $g_stmt->insert_id;
            $g_stmt->close();

            [$cdob_enc, $cdob_iv, $cdob_tag] = encryptField($childDob);

            $c_stmt = $conn->prepare("
                INSERT INTO users
                (username, password, last_name, first_name, middle_name, gender,
                date_of_birth, date_of_birth_iv, date_of_birth_tag,
                email, contact_number, contact_number_iv, contact_number_tag,
                role, status, branch_id, guardian_id, relationship)
                VALUES (NULL, NULL, ?, ?, NULL, ?, ?, ?, ?, NULL, NULL, NULL, NULL,
                        'patient', 'Active', ?, ?, ?)
            ");

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
            $patient_user_id = $c_stmt->insert_id;
            $c_stmt->close();
        }

        $appointment_sql = "
            INSERT INTO appointment_transaction 
            (user_id, branch_id, dentist_id, appointment_date, appointment_time, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Booked')
        ";
        $appointment_stmt = $conn->prepare($appointment_sql);
        $appointment_stmt->bind_param(
            "iiisss",
            $patient_user_id,
            $appointmentBranch,
            $appointmentDentist,
            $appointmentDate,
            $appointmentTime,
            $notes
        );
        $appointment_stmt->execute();
        $appointment_id = $appointment_stmt->insert_id;
        $appointment_stmt->close();

        if (!empty($appointmentServices) && is_array($appointmentServices)) {

            $quantities = $_POST['serviceQuantity'] ?? [];

            $service_sql = "INSERT INTO appointment_services 
                            (appointment_transaction_id, service_id, quantity) 
                            VALUES (?, ?, ?)";
            $service_stmt = $conn->prepare($service_sql);

            foreach ($appointmentServices as $serviceId) {
                $sid = (int)$serviceId;
                $qty = isset($quantities[$sid]) && (int)$quantities[$sid] > 0 ? (int)$quantities[$sid] : 1;
                $service_stmt->bind_param("iii", $appointment_id, $sid, $qty);
                $service_stmt->execute();
            }

            $service_stmt->close();
        }

        $welcome_msg = "Welcome to Smile-ify! Your account was created.";

        $notifWelcome = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notifWelcome->bind_param("is", $guardian_id, $welcome_msg);
        $notifWelcome->execute();
        $notifWelcome->close();

        $childMsg = "Your appointment on $appointmentDate at $appointmentTime was successfully booked.";
        $guardianMsg = "Your dependent's appointment on $appointmentDate at $appointmentTime was successfully booked.";

        $notifChild = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notifChild->bind_param("is", $patient_user_id, $childMsg);
        $notifChild->execute();
        $notifChild->close();

        if ($bookingType === "child") {
            $notifGuardian = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notifGuardian->bind_param("is", $guardian_id, $guardianMsg);
            $notifGuardian->execute();
            $notifGuardian->close();
        }

        $conn->commit();

        $servicesHtml = "";
        $totalPrice = 0;
        $totalDuration = 0;

        if (!empty($appointmentServices)) {

            $quantities = $_POST['serviceQuantity'] ?? [];

            $placeholders = implode(',', array_fill(0, count($appointmentServices), '?'));
            $types = str_repeat('i', count($appointmentServices));

            $stmt = $conn->prepare("
                SELECT service_id, name, price, duration_minutes 
                FROM service 
                WHERE service_id IN ($placeholders)
            ");

            $stmt->bind_param($types, ...$appointmentServices);
            $stmt->execute();
            $result = $stmt->get_result();

            $servicesHtml .= "<ul>";

            while ($row = $result->fetch_assoc()) {

                $sid = $row['service_id'];

                $qty = isset($quantities[$sid]) && (int)$quantities[$sid] > 0 ? (int)$quantities[$sid] : 1;

                $linePrice = $row['price'] * $qty;
                $lineDuration = $row['duration_minutes'] * $qty;

                $servicesHtml .= "<li>{$row['name']} (x{$qty}) – ₱" 
                                . number_format($linePrice, 2) . 
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
            <p>Your Smile-ify account has been successfully created.</p>
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
                <strong>Appointment ID:</strong> $appointment_id<br>
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

        if (!$mail->send()) throw new Exception("Mailer Error: " . $mail->ErrorInfo);

        $_SESSION['updateSuccess'] = "Walk-in patient booked and credentials emailed successfully.";
        header("Location: " . BASE_URL . "/Admin/pages/calendar.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error booking walk-in appointment: " . $e->getMessage());
        $_SESSION['updateError'] = "Failed to book walk-in appointment. Please try again.";
        header("Location: " . BASE_URL . "/Admin/index.php");
        exit;
    }
}
?>
