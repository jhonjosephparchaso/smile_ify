<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "No secretary ID provided"]);
    exit();
}

$adminId = intval($_GET['id']);

$sql = "SELECT 
            u.user_id,
            u.username,
            u.last_name,    
            u.first_name,
            u.middle_name,
            CONCAT(u.last_name, ', ', u.first_name, ' ', IFNULL(u.middle_name, '')) AS name,
            u.gender,
            u.date_of_birth,
            u.date_of_birth_iv,
            u.date_of_birth_tag,
            u.email,
            u.contact_number,
            u.contact_number_iv,
            u.contact_number_tag,
            u.address,
            u.address_iv,
            u.address_tag,
            b.branch_id,
            b.name AS branch,
            u.status,
            u.date_started,
            u.date_updated,
            u.date_created
        FROM users u
        LEFT JOIN branch b ON u.branch_id = b.branch_id
        WHERE u.user_id = ? 
        AND u.role = 'admin'
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (!empty($row['date_of_birth']) && !empty($row['date_of_birth_iv']) && !empty($row['date_of_birth_tag'])) {
        $row['date_of_birth'] = decryptField($row['date_of_birth'], $row['date_of_birth_iv'], $row['date_of_birth_tag'], $ENCRYPTION_KEY);
    }

    if (!empty($row['contact_number']) && !empty($row['contact_number_iv']) && !empty($row['contact_number_tag'])) {
        $row['contact_number'] = decryptField($row['contact_number'], $row['contact_number_iv'], $row['contact_number_tag'], $ENCRYPTION_KEY);
    }

    if (!empty($row['address']) && !empty($row['address_iv']) && !empty($row['address_tag'])) {
        $row['address'] = decryptField($row['address'], $row['address_iv'], $row['address_tag'], $ENCRYPTION_KEY);
    }

    unset(
        $row['date_of_birth_iv'], $row['date_of_birth_tag'],
        $row['contact_number_iv'], $row['contact_number_tag'],
        $row['address_iv'], $row['address_tag']
    );

    echo json_encode($row);
} else {
    echo json_encode(["error" => "Secretary not found"]);
}

$conn->close();
?>
