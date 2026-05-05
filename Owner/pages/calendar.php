<?php
session_start();

$currentPage = 'calendar';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/index.php");
    exit();
}
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Owner/includes/navbar.php';
?>
<title>Calendar</title>

<div class="calendar-container">
    <div id="calendarLegend" class="legend"></div>
    <div id="calendar"></div>
</div>

<div id="appointmentModalDetails" class="manage-calendar-modal">
    <div class="manage-calendar-modal-content">
        <div id="modalBody" class="manage-calendar-modal-content-body">
            <!-- Appointment info will be loaded here -->
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>