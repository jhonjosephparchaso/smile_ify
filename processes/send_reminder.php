<?php
if (php_sapi_name() === 'cli') {
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['REQUEST_URI'] = '/Smile-ify/processes/send_reminder.php';
    $_SERVER['DOCUMENT_ROOT'] = 'D:/xampp/htdocs';
}

require_once __DIR__ . '/../includes/config.php';
require_once BASE_PATH . '/includes/db.php';
require_once BASE_PATH . '/includes/mail_function.php';

date_default_timezone_set('Asia/Manila');

$logFile = BASE_PATH . '/Mail/phpmailer/reminder_log.txt';
file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] Script started\n", FILE_APPEND);

$hoursBefore = 24;
$minutesBefore = 0;
$windowMinutes = 30; 

$now = new DateTime();
$targetStart = (clone $now)->modify("+{$hoursBefore} hours +{$minutesBefore} minutes");
$targetEnd   = (clone $now)->modify("+{$hoursBefore} hours +{$minutesBefore} minutes +{$windowMinutes} minutes");

$currentDateTime = $targetStart->format('Y-m-d H:i:s');
$nextDateTime = $targetEnd->format('Y-m-d H:i:s');

file_put_contents(
    $logFile,
    '[' . date('Y-m-d H:i:s') . "] Checking between $currentDateTime and $nextDateTime\n",
    FILE_APPEND
);

$stmt = $conn->prepare("
    SELECT 
        at.appointment_transaction_id,
        at.appointment_date,
        at.appointment_time,
        
        u.user_id AS patient_id,
        u.first_name AS p_first,
        u.middle_name AS p_middle,
        u.last_name AS p_last,
        u.email AS patient_email,
        u.guardian_id,

        g.email AS guardian_email,
        g.first_name AS g_first,
        g.middle_name AS g_middle,
        g.last_name AS g_last,

        b.address
    FROM appointment_transaction at
    JOIN users u ON at.user_id = u.user_id
    LEFT JOIN users g ON u.guardian_id = g.user_id
    JOIN branch b ON at.branch_id = b.branch_id
    WHERE at.status = 'Booked'
        AND at.reminder_sent = 0
        AND CONCAT(at.appointment_date, ' ', at.appointment_time)
            BETWEEN ? AND ?
");

$stmt->bind_param('ss', $currentDateTime, $nextDateTime);
$stmt->execute();
$result = $stmt->get_result();

$sentCount = 0;

while ($appt = $result->fetch_assoc()) {

    $isDependent = !empty($appt['guardian_id']);

    if ($isDependent) {
        $recipientEmail = $appt['guardian_email'];
        $recipientName = trim("{$appt['g_first']} {$appt['g_middle']} {$appt['g_last']}");
        $dependentName = trim("{$appt['p_first']} {$appt['p_middle']} {$appt['p_last']}");
        $intro = "This is a reminder that your dependent <b>{$dependentName}</b> has a dental appointment scheduled for:";
    } else {
        $recipientEmail = $appt['patient_email'];
        $recipientName = trim("{$appt['p_first']} {$appt['p_middle']} {$appt['p_last']}");
        $intro = "This is a friendly reminder for your dental appointment scheduled for:";
    }

    if (!$recipientEmail) continue;

    $apptDateTime = date('F j, Y g:i A', strtotime($appt['appointment_date'].' '.$appt['appointment_time']));
    $branch = $appt['address'];
    $subject = "Dental Appointment Reminder";

    $message = "
        <p>Hi {$recipientName},</p>
        <p>{$intro}</p>
        <p><b>{$apptDateTime}</b> at <b>{$branch}</b>.</p>
        <p>Please contact us if you need to reschedule or confirm your visit.</p>
        <p>Thank you,<br><b>Smile Dental Clinic</b></p>
    ";

    if (sendMail($recipientEmail, $subject, $message)) {

        $update = $conn->prepare("
            UPDATE appointment_transaction 
            SET reminder_sent = 1 
            WHERE appointment_transaction_id = ?
        ");
        $update->bind_param('i', $appt['appointment_transaction_id']);
        $update->execute();
        $update->close();

        $sentCount++;
        file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] Reminder sent to {$recipientEmail}\n", FILE_APPEND);
    }
}

$stmt->close();
$conn->close();

$output = "Reminders checked at " . date('Y-m-d H:i:s') . ". Sent to {$sentCount} recipient(s).";
echo $output;
file_put_contents($logFile, $output . "\n\n", FILE_APPEND);
?>
