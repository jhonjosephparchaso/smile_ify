<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qrImage'])) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/images/qr/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileExt = strtolower(pathinfo($_FILES['qrImage']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($fileExt, $allowed)) {
        foreach (glob($uploadDir . 'qr_payment.*') as $oldFile) {
            unlink($oldFile);
        }

        $fileName = 'qr_payment.' . $fileExt;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['qrImage']['tmp_name'], $targetPath)) {
            $relativePath = '/images/qr/' . $fileName;

            $stmt = $conn->prepare("SELECT id FROM qr_payment LIMIT 1");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $conn->query("UPDATE qr_payment SET file_name='$fileName', file_path='$relativePath', uploaded_at=NOW() WHERE id=1");
            } else {
                $conn->query("INSERT INTO qr_payment (file_name, file_path) VALUES ('$fileName', '$relativePath')");
            }

            $_SESSION['updateSuccess'] = "QR payment image uploaded successfully!";
        } else {
            $_SESSION['updateError'] = "Failed to upload the QR payment image.";
        }
    } else {
        $_SESSION['updateError'] = "Invalid file type. Only JPG, PNG, or WEBP allowed.";
    }
}

header("Location: " . BASE_URL . "/Owner/pages/profile.php");
exit();
?>
