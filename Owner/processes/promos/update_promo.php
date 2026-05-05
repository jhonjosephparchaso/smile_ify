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

    $promo_id       = intval($_POST["promo_id"]);
    $name           = trim($_POST["promoName"]);
    $description    = trim($_POST["description"]);
    $discount_type  = trim($_POST["discountType"]);
    $discount_value = floatval($_POST["discountValue"]);
    $start_date     = !empty($_POST["startDate"]) ? $_POST["startDate"] : null;
    $end_date       = !empty($_POST["endDate"]) ? $_POST["endDate"] : null;
    $branches       = isset($_POST["branches"]) ? $_POST["branches"] : [];
    $promoImageCleared = isset($_POST['promoImageCleared']) && $_POST['promoImageCleared'] == '1';

    if (!$promo_id || empty($name) || empty($discount_type) || $discount_value < 0) {
        $_SESSION['updateError'] = "Invalid or missing promo details.";
        header("Location: " . BASE_URL . "/Owner/pages/promos.php");
        exit;
    }

    try {
        $conn->begin_transaction();
        
        $checkSQL = "SELECT name, description, discount_type, discount_value, image_path FROM promo WHERE promo_id = ?";
        $checkStmt = $conn->prepare($checkSQL);
        $checkStmt->bind_param("i", $promo_id);
        $checkStmt->execute();
        $oldData = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/images/promos/';
        $image_path = $oldData['image_path'] ?? null;
        $hasChanges = false;

        if ($promoImageCleared && $image_path) {
            $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify' . $image_path;
            if (file_exists($absolutePath)) unlink($absolutePath);
            $image_path = null;
            $hasChanges = true;

            $clearStmt = $conn->prepare("UPDATE promo SET image_path = NULL, date_updated = NOW() WHERE promo_id = ?");
            $clearStmt->bind_param("i", $promo_id);
            $clearStmt->execute();
            $clearStmt->close();
        }

        if (isset($_FILES['promoImage']) && $_FILES['promoImage']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxFileSize  = 5 * 1024 * 1024;
            $fileTmpPath  = $_FILES['promoImage']['tmp_name'];
            $fileType     = mime_content_type($fileTmpPath);
            $fileSize     = $_FILES['promoImage']['size'];

            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['updateError'] = "Invalid image type. Allowed: JPG, PNG, WEBP.";
                header("Location: " . BASE_URL . "/Owner/pages/promos.php");
                exit;
            }

            if ($fileSize > $maxFileSize) {
                $_SESSION['updateError'] = "Image size exceeds 5MB limit.";
                header("Location: " . BASE_URL . "/Owner/pages/promos.php");
                exit;
            }

            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $extension = strtolower(pathinfo($_FILES["promoImage"]["name"], PATHINFO_EXTENSION));
            $fileName  = "promo_" . $promo_id . "." . $extension;
            $targetPath = $uploadDir . $fileName;

            $oldFiles = glob($uploadDir . "promo_" . $promo_id . ".*");
            foreach ($oldFiles as $oldFile) if (is_file($oldFile)) unlink($oldFile);

            if (move_uploaded_file($fileTmpPath, $targetPath)) {
                $image_path = "/images/promos/" . $fileName;
                $hasChanges = true;
            }
        }

        if ($oldData['name'] !== $name ||
            $oldData['description'] !== $description ||
            $oldData['discount_type'] !== $discount_type ||
            floatval($oldData['discount_value']) !== $discount_value ||
            $image_path !== $oldData['image_path']) {

            $hasChanges = true;

            $sql = "UPDATE promo 
                    SET name = ?, description = ?, discount_type = ?, discount_value = ?, image_path = ?, date_updated = NOW()
                    WHERE promo_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdsi", $name, $description, $discount_type, $discount_value, $image_path, $promo_id);
            $stmt->execute();
            $stmt->close();
        }

        $existingBranches = [];
        $branchCheckSQL = "SELECT branch_id FROM branch_promo WHERE promo_id = ?";
        $branchCheckStmt = $conn->prepare($branchCheckSQL);
        $branchCheckStmt->bind_param("i", $promo_id);
        $branchCheckStmt->execute();
        $result = $branchCheckStmt->get_result();
        while ($row = $result->fetch_assoc()) $existingBranches[] = intval($row['branch_id']);
        $branchCheckStmt->close();

        sort($existingBranches);
        $newBranches = array_map('intval', $branches);
        sort($newBranches);

        if ($existingBranches !== $newBranches) {
            $hasChanges = true;
            $deleteSQL = "DELETE FROM branch_promo WHERE promo_id = ?";
            $delStmt = $conn->prepare($deleteSQL);
            $delStmt->bind_param("i", $promo_id);
            $delStmt->execute();
            $delStmt->close();

            if (!empty($newBranches)) {
                $insertSQL = "INSERT INTO branch_promo (branch_id, promo_id, start_date, end_date)
                                VALUES (?, ?, ?, ?)";
                $insStmt = $conn->prepare($insertSQL);
                foreach ($newBranches as $branch_id) {
                    $insStmt->bind_param("iiss", $branch_id, $promo_id, $start_date, $end_date);
                    $insStmt->execute();
                }
                $insStmt->close();
            }

            $conn->query("UPDATE promo SET date_updated = NOW() WHERE promo_id = $promo_id");
        }

        $conn->commit();

        $updateDatesStmt = $conn->prepare("
            UPDATE branch_promo
            SET start_date = ?, end_date = ?
            WHERE promo_id = ?
        ");
        $updateDatesStmt->bind_param("ssi", $start_date, $end_date, $promo_id);
        $updateDatesStmt->execute();
        $updateDatesStmt->close();

        if ($hasChanges) {
            $conn->query("UPDATE promo SET date_updated = NOW() WHERE promo_id = $promo_id");
            $_SESSION['updateSuccess'] = "Promo updated successfully!";
        }
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
?>
