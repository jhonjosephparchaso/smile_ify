<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$transactionId = $_GET['id'] ?? null;

$response = ["success" => false];

if (!$transactionId) {
    echo json_encode($response);
    exit;
}

$stmt = $conn->prepare("
    SELECT xray_file, date_updated
    FROM dental_transaction
    WHERE dental_transaction_id = ?
");

if (!$stmt) {
    echo json_encode(["success" => false, "error" => $conn->error]);
    exit;
}

$stmt->bind_param("i", $transactionId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data && !empty($data['xray_file'])) {

    $response["success"] = true;
    $response["files"] = [
        [
            "file_path"   => $data["xray_file"],
            "date_created" => $data["date_updated"]
        ]
    ];
}

if ($conn->error) {
    echo $conn->error;
    exit;
}

echo json_encode($response);
?>
