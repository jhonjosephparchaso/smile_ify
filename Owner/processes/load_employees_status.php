<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

$employeeStats = [
    'admins' => ['active' => 0, 'inactive' => 0],
    'dentists' => ['active' => 0, 'inactive' => 0],
    'total' => ['active' => 0, 'inactive' => 0],
];

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'owner') {

    $sqlAdmins = "
        SELECT 
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_admins,
            SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) AS inactive_admins
        FROM users
        WHERE role = 'admin'
    ";
    $resultAdmins = $conn->query($sqlAdmins);
    if ($resultAdmins && $resultAdmins->num_rows > 0) {
        $row = $resultAdmins->fetch_assoc();
        $employeeStats['admins']['active'] = (int)$row['active_admins'];
        $employeeStats['admins']['inactive'] = (int)$row['inactive_admins'];
        $employeeStats['total']['active'] += (int)$row['active_admins'];
        $employeeStats['total']['inactive'] += (int)$row['inactive_admins'];
    }

    $sqlDentists = "
        SELECT 
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_dentists,
            SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) AS inactive_dentists
        FROM dentist
    ";
    $resultDentists = $conn->query($sqlDentists);
    if ($resultDentists && $resultDentists->num_rows > 0) {
        $row = $resultDentists->fetch_assoc();
        $employeeStats['dentists']['active'] = (int)$row['active_dentists'];
        $employeeStats['dentists']['inactive'] = (int)$row['inactive_dentists'];
        $employeeStats['total']['active'] += (int)$row['active_dentists'];
        $employeeStats['total']['inactive'] += (int)$row['inactive_dentists'];
    }
}
?>
