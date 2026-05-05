<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    echo json_encode(["data" => []]);
    exit();
}

$sql = "SELECT 
            branch_id,
            name,
            address,
            dental_chairs,
            phone_number,
            status
        FROM branch";

$result = $conn->query($sql);

$branches = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $branches[] = [
            $row['branch_id'],
            $row['name'],
            $row['dental_chairs'],
            $row['phone_number'],
            $row['status'],
            '<button class="btn-branch" data-type="branch" data-id="'.$row['branch_id'].'">Manage</button>'
        ];
    }
}

echo json_encode(["data" => $branches]);
$conn->close();
