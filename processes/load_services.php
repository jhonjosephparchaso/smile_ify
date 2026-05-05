<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

header("Content-Type: text/html; charset=UTF-8");

if (!isset($_POST['appointmentBranch'])) {
    echo "<p class='error-msg'>Missing branch.</p>";
    exit;
}

$branchId = intval($_POST['appointmentBranch']);
$transactionId = $_POST['appointment_transaction_id'] ?? null;
$appointmentId = $_POST['appointment_id'] ?? null;

$selectedServices = [];

if ($transactionId) {
    $stmt = $conn->prepare("
        SELECT service_id, quantity
        FROM dental_transaction_services
        WHERE dental_transaction_id = ?
    ");
    $stmt->bind_param("i", $transactionId);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $selectedServices[(int)$row['service_id']] = (int)$row['quantity'];
    }
    $stmt->close();
}

elseif ($appointmentId) {
    $stmt = $conn->prepare("
        SELECT service_id
        FROM appointment_services
        WHERE appointment_transaction_id = ?
    ");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $selectedServices[(int)$row['service_id']] = 1;  
    }
    $stmt->close();
}

$stmt = $conn->prepare("
    SELECT s.service_id, s.name, s.price, s.duration_minutes, s.requires_xray
    FROM service s
    INNER JOIN branch_service bs ON s.service_id = bs.service_id
    WHERE bs.branch_id = ? AND bs.status = 'Active'
    ORDER BY s.service_id ASC
");
$stmt->bind_param("i", $branchId);
$stmt->execute();
$servicesResult = $stmt->get_result();

if ($servicesResult->num_rows == 0) {
    echo "<p class='error-msg'>No services available for this branch.</p>";
    exit;
}

while ($row = $servicesResult->fetch_assoc()) {
    $serviceId = (int)$row['service_id'];
    $serviceName = htmlspecialchars($row['name']);
    $formattedPrice = number_format((float)$row['price'], 2);
    $duration = htmlspecialchars($row['duration_minutes']);
    $requiresXray = (int)$row['requires_xray'];

    $isChecked = isset($selectedServices[$serviceId]);
    $qty = $isChecked ? $selectedServices[$serviceId] : 1;

    $durationHtml = empty($_POST['hide_duration'])
        ? "<small class='duration'>({$duration} mins)</small>"
        : "";

    echo "
    <div class='checkbox-item'>
        <label>
            <input 
                type='checkbox' 
                id='service_{$serviceId}' 
                name='appointmentServices[]' 
                value='{$serviceId}'
                data-duration='{$duration}'
                data-requires-xray='{$requiresXray}'
                data-service-name=\"{$serviceName}\"
                " . ($isChecked ? "checked" : "") . "
            >
            {$serviceName}
            <span class='price'>₱{$formattedPrice}</span>
            {$durationHtml}
        </label>

        <input 
            type='number' 
            name='serviceQuantity[{$serviceId}]' 
            class='service-quantity' 
            min='1' 
            value='{$qty}'
            style='display: " . ($isChecked ? "inline-block" : "none") . "; width:60px; margin-left:10px;'
        >
    </div>";
}

$stmt->close();
$conn->close();
