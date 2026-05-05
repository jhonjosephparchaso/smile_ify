<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    $_SESSION['updateError'] = "Unauthorized access.";
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$branchName   = trim($_POST["branchName"] ?? "");
$nickname     = trim($_POST["nickname"] ?? "");
$address      = trim($_POST["address"] ?? "");
$phone_number = trim($_POST["contactNumber"] ?? "");
$map_url      = trim($_POST["map_url"] ?? "");
$status       = $_POST["status"] ?? "Active";
$dental_chairs = intval($_POST["chairCount"] ?? 1);

try {
    $sql = "INSERT INTO branch
            (name, nickname, address, phone_number, dental_chairs, status, map_url, date_created, date_updated)
            VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, ''), NOW(), NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssiss",
        $branchName,
        $nickname,
        $address,
        $phone_number,
        $dental_chairs,
        $status,
        $map_url
    );

    if ($stmt->execute()) {
        $_SESSION['updateSuccess'] = "Branch added successfully!";
    } else {
        $_SESSION['updateError'] = "Failed to add branch: " . $stmt->error;
    }

    $stmt->close();
} catch (Exception $e) {
    $_SESSION['updateError'] = "Database error: " . $e->getMessage();
}

header("Location: " . BASE_URL . "/Owner/pages/profile.php");
exit;

$conn->close();
