<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$stmt = $conn->prepare("
    SELECT branch_id, name, status
    FROM branch
    WHERE status = 'Active'
    ORDER BY name ASC
");
$stmt->execute();
$result = $stmt->get_result();

$options = '<option value="" disabled selected hidden></option>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $options .= "<option value='" . $row['branch_id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
    }
} else {
    $options .= '<option disabled>No branches available</option>';
}

echo $options;

$stmt->close();
$conn->close();
