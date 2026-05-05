<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$userID = $_SESSION['user_id'];

$sql = "SELECT 
            first_name, 
            middle_name, 
            last_name, 
            gender, 
            date_of_birth,
            date_of_birth_iv,
            date_of_birth_tag,
            email, 
            contact_number,
            contact_number_iv,
            contact_number_tag,
            address,
            address_iv,
            address_tag,
            date_created,
            date_updated 
        FROM users 
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $decryptedDOB = null;
    if (!empty($row['date_of_birth']) && !empty($row['date_of_birth_iv']) && !empty($row['date_of_birth_tag'])) {
        $decryptedDOB = decryptField(
            $row['date_of_birth'],
            $row['date_of_birth_iv'],
            $row['date_of_birth_tag']
        );
    }

    $decryptedContact = null;
    if (!empty($row['contact_number']) && !empty($row['contact_number_iv']) && !empty($row['contact_number_tag'])) {
        $decryptedContact = decryptField(
            $row['contact_number'],
            $row['contact_number_iv'],
            $row['contact_number_tag']
        );
    }

    $decryptedAddress = null;
    if (!empty($row['address']) && !empty($row['address_iv']) && !empty($row['address_tag'])) {
        $decryptedAddress = decryptField(
            $row['address'],
            $row['address_iv'],
            $row['address_tag']
        );
    }

    $formattedDOB = $decryptedDOB 
        ? date("F j, Y", strtotime($decryptedDOB)) 
        : '-';

    $profile = [
        'full_name'      => trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
        'gender'         => ucfirst($row['gender'] ?? '-'),
        'date_of_birth'  => $formattedDOB,
        'email'          => $row['email'] ?? '-',
        'contact_number' => $decryptedContact ?? '-',
        'address'        => $decryptedAddress ?? '-',
        'joined'         => !empty($row['date_created']) ? date("F j, Y", strtotime($row['date_created'])) : '-',
        'date_updated'   => !empty($row['date_updated']) ? date("F j, Y", strtotime($row['date_updated'])) : '-',
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($profile);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found.']);
}

$conn->close();
?>
