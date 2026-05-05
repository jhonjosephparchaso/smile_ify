<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

function isValidEmailDomain($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, "MX");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION['verified_data'] = $_POST;
    $_SESSION['verified_data']['serviceQuantity'] =
    isset($_POST['serviceQuantity']) ? $_POST['serviceQuantity'] : [];

    $email = trim($_POST["email"]);

    if (!isValidEmailDomain($email)) {
        $_SESSION['otp_error'] = "Email domain is not valid or unreachable.";
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }

    if (empty($_POST['appointmentServices']) || !is_array($_POST['appointmentServices'])) {
        $_SESSION['otp_error'] = "Please select at least one service.";
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }

    $serviceIds = $_POST['appointmentServices'];
    $quantities = isset($_POST['serviceQuantity']) ? $_POST['serviceQuantity'] : [];
    $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
    $types = str_repeat('i', count($serviceIds));

    $stmt = $conn->prepare("
        SELECT s.service_id, s.name, s.price
        FROM service s
        INNER JOIN branch_service bs ON s.service_id = bs.service_id
        WHERE s.service_id IN ($placeholders)
    ");

    $stmt->bind_param($types, ...$serviceIds);
    $stmt->execute();
    $result = $stmt->get_result();

    $selectedServices = [];
    $totalPrice = 0;

    while ($row = $result->fetch_assoc()) {

        $id  = $row['service_id'];

        $qty = isset($quantities[$id]) && (int)$quantities[$id] > 0
            ? (int)$quantities[$id]
            : 1;

        $linePrice = $row['price'] * $qty;

        $selectedServices[] = [
            'id'    => $id,
            'name'  => $row['name'],
            'price' => number_format((float)$row['price'], 2),
            'qty'   => $qty,
            'line_price' => $linePrice
        ];

        $totalPrice += $linePrice;
    }

    $_SESSION['selected_services'] = $selectedServices;
    $_SESSION['total_price'] = $totalPrice;

    $stmt->close();

    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_created'] = time();
    $_SESSION['mail'] = $email;

    require BASE_PATH . '/Mail/phpmailer/PHPMailerAutoload.php';
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->Port       = SMTP_PORT;
    $mail->SMTPAuth   = SMTP_AUTH;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;

    $mail->setFrom('smileify.web@gmail.com', 'Smile-ify OTP Verification');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Smile-ify Verification Code";

    $servicesHtml = "<ul>";
    foreach ($selectedServices as $s) {
        $servicesHtml .= "<li>{$s['name']} - ₱{$s['price']}</li>";
    }
    $servicesHtml .= "</ul>";

    $totalFormatted = number_format($totalPrice, 2);
    
    $mail->Body = "
        <p>Dear Customer/Patient,</p>
        <p>Your One-Time Password (OTP) is:</p>
        <h3>$otp</h3>
        <br>
        <p><i>Smile with confidence.</i></p>
        <p>Best regards,<br><strong>Smile-ify</strong></p>
    ";
    if (!$mail->send()) {
        $_SESSION['otp_error'] = "Failed to send OTP. Please try again.";
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }

    header("Location: " . BASE_URL . "/includes/OTP Includes/otp_verification.php");
    exit();
}
?>
