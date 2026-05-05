<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$branchId = intval($_POST['appointmentBranch'] ?? 0);
$serviceIds = $_POST['appointmentServices'] ?? [];

if (!$branchId) {
    echo '<option value="" disabled selected hidden>No dentist available</option>';
    exit;
}

if (empty($serviceIds)) {
    $sql = "
        SELECT d.dentist_id, d.first_name, d.last_name
        FROM dentist d
        INNER JOIN dentist_branch db ON d.dentist_id = db.dentist_id
        WHERE db.branch_id = ?
        AND d.status = 'Active'
        ORDER BY d.first_name
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branchId);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="" disabled selected hidden>Select dentist</option>';

    while ($row = $result->fetch_assoc()) {
        $name = "Dr. " . $row['first_name'] . " " . $row['last_name'];
        echo "<option value='{$row['dentist_id']}'>$name</option>";
    }

    $stmt->close();
    $conn->close();
    exit;
}

$placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
$types = str_repeat('i', count($serviceIds));

$sql = "
    SELECT DISTINCT d.dentist_id, d.first_name, d.last_name
    FROM dentist d
    INNER JOIN dentist_branch db ON d.dentist_id = db.dentist_id
    WHERE db.branch_id = ?
    AND d.status = 'Active'
    AND NOT EXISTS (
        SELECT 1
        FROM service s
        WHERE s.service_id IN ($placeholders)
        AND s.service_id NOT IN (
            SELECT ds.service_id FROM dentist_service ds WHERE ds.dentist_id = d.dentist_id
        )
    )
";

$stmt = $conn->prepare($sql);

$bindTypes = 'i' . $types;
$params = array_merge([$branchId], $serviceIds);
$refs = [$bindTypes];

foreach ($params as $key => $value) {
    $refs[] = &$params[$key];
}

call_user_func_array([$stmt, 'bind_param'], $refs);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<option value="" disabled selected hidden>No qualified dentist</option>';
    exit;
}

echo '<option value="" disabled selected hidden>Select dentist</option>';

while ($row = $result->fetch_assoc()) {
    $name = "Dr. " . $row['first_name'] . " " . $row['last_name'];
    echo "<option value='{$row['dentist_id']}'>$name</option>";
}

$stmt->close();
$conn->close();
?>
