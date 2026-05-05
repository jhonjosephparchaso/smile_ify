<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Missing transaction ID']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT medcert_receipt, fitness_status, diagnosis, remarks
        FROM dental_transaction
        WHERE dental_transaction_id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        echo json_encode([
            'success' => true,
            'medcert_receipt' => $data['medcert_receipt'],
            'fitness_status' => $data['fitness_status'],
            'diagnosis' => $data['diagnosis'],
            'remarks' => $data['remarks']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Transaction not found']);
    }

    $stmt->close();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
