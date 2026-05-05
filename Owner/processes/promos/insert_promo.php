<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
        $_SESSION['updateError'] = "Unauthorized access.";
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }

    $name           = trim($_POST["promoName"]);
    $description    = trim($_POST["description"]);
    $discount_type  = trim($_POST["discountType"]);
    $discount_value = floatval($_POST["discountValue"]);
    $start_date     = !empty($_POST["startDate"]) ? $_POST["startDate"] : null;
    $end_date       = !empty($_POST["endDate"]) ? $_POST["endDate"] : null;
    $branches       = isset($_POST["branches"]) ? $_POST["branches"] : [];
    $image_path     = null;

    if (empty($name) || $discount_value < 0) {
        $_SESSION['updateError'] = "Please fill in all required fields correctly.";
        header("Location: " . BASE_URL . "/Owner/pages/promos.php");
        exit;
    }

    try {
        $conn->begin_transaction();

        $sql = "INSERT INTO promo (name, description, discount_type, discount_value, date_created, date_updated)
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed (promo): " . $conn->error);
        }

        $stmt->bind_param("sssd", $name, $description, $discount_type, $discount_value);
        $stmt->execute();
        $promo_id = $stmt->insert_id;
        $stmt->close();

        if (isset($_FILES['promoImage']) && $_FILES['promoImage']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxFileSize  = 5 * 1024 * 1024;
            $fileTmpPath  = $_FILES['promoImage']['tmp_name'];
            $fileType     = mime_content_type($fileTmpPath);
            $fileSize     = $_FILES['promoImage']['size'];

            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Invalid image type. Allowed: JPG, PNG, WEBP.");
            }

            if ($fileSize > $maxFileSize) {
                throw new Exception("Image size exceeds 5MB limit.");
            }

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/images/promos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension  = strtolower(pathinfo($_FILES["promoImage"]["name"], PATHINFO_EXTENSION));
            $fileName   = "promo_" . $promo_id . "." . $extension;
            $targetPath = $uploadDir . $fileName;

            foreach (glob($uploadDir . "promo_" . $promo_id . ".*") as $oldFile) {
                if (is_file($oldFile)) unlink($oldFile);
            }

            if (move_uploaded_file($fileTmpPath, $targetPath)) {
                $image_path = "/images/promos/" . $fileName;

                $updateImage = $conn->prepare("UPDATE promo SET image_path = ? WHERE promo_id = ?");
                $updateImage->bind_param("si", $image_path, $promo_id);
                $updateImage->execute();
                $updateImage->close();
            }
        }

        if (!empty($branches)) {
            $sql2 = "INSERT INTO branch_promo 
                        (branch_id, promo_id, start_date, end_date) 
                        VALUES (?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            if (!$stmt2) {
                throw new Exception("Prepare failed (branch_promo): " . $conn->error);
            }

            foreach ($branches as $branch_id) {
                $branch_id = intval($branch_id);
                $stmt2->bind_param("iiss", $branch_id, $promo_id, $start_date, $end_date);
                $stmt2->execute();
            }
            $stmt2->close();
        }

        $conn->commit();
        $_SESSION['updateSuccess'] = "Promo added successfully!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['updateError'] = "Database error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/Owner/pages/promos.php");
    exit;

} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$conn->close();
