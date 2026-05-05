<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = $_GET['id'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['error' => 'No patient ID provided']);
    exit();
}

$sql = "
    SELECT 
        user_id, 
        guardian_id,
        relationship,
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
        date_updated, 
        status 
    FROM users 
    WHERE user_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $userId);
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

    $isDependent = !empty($row['guardian_id']);

    $guardianInfo = null;
    if ($isDependent) {
        $g = $conn->prepare("
            SELECT 
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
                address_tag
            FROM users 
            WHERE user_id = ?
        ");
        $g->bind_param("i", $row['guardian_id']);
        $g->execute();
        $gRes = $g->get_result();

        if ($gRes && $gRow = $gRes->fetch_assoc()) {

            $gDOB = decryptField(
                $gRow['date_of_birth'],
                $gRow['date_of_birth_iv'],
                $gRow['date_of_birth_tag']
            );

            $gContact = decryptField(
                $gRow['contact_number'],
                $gRow['contact_number_iv'],
                $gRow['contact_number_tag']
            );

            $gAddress = decryptField(
                $gRow['address'],
                $gRow['address_iv'],
                $gRow['address_tag']
            );

            $guardianInfo = [
                "full_name" => trim(($gRow['first_name'] ?? '') . ' ' . ($gRow['middle_name'] ?? '') . ' ' . ($gRow['last_name'] ?? '')),
                "gender" => ucfirst($gRow['gender']),
                "dob" => $gDOB ? date("F j, Y", strtotime($gDOB)) : "-",
                "email" => $gRow['email'] ?? "-",
                "contact_number" => $gContact ?? "-",
                "address" => $gAddress ?? "-"
            ];
        }
    }

    $data = [
        'full_name'      => trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
        'relationship' => $row['relationship'] ? ucfirst($row['relationship']) : null,
        'gender'         => ucfirst($row['gender'] ?? '-'),
        'date_of_birth'  => $decryptedDOB ? date("F j, Y", strtotime($decryptedDOB)) : '-',
        'email'          => $row['email'] ?? '-',
        'contact_number' => $decryptedContact ?? '-',
        'address'        => $decryptedAddress ?? '-',
        'joined'         => !empty($row['date_created']) ? date("F j, Y", strtotime($row['date_created'])) : '-',
        'date_updated'   => !empty($row['date_updated']) ? date("F j, Y", strtotime($row['date_updated'])) : '-',
        "status"         => ucfirst($row['status']),
        "is_dependent"   => $isDependent,
        "guardian_info"  => $guardianInfo
    ];

    echo json_encode($data);
} 
else {
    http_response_code(404);
    echo json_encode(['error' => 'Patient not found']);
}

$stmt->close();
$conn->close();
?>
