<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$currentPage = 'index';

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/Owner/includes/navbar.php';
?>

<title>Home</title>

<div class="dashboard">

    <div class="cards">
        
        <div class="card">
            <h2><span class="material-symbols-outlined">monitoring</span> Revenue This Month</h2>
            <div id="revenueThisMonthContainer">Loading...</div>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">apartment</span> Branch Performance</h2>
            <div id="branchPerformanceContainer">Loading...</div>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">calendar_month</span> Appointments Overview</h2>
            <div id="appointmentsOverviewContainer">Loading...</div>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">groups</span> Employees</h2>
            <div class="announcement"><strong>Secretaries</strong> - Active: <?= htmlspecialchars($employeeStats['admins']['active']) ?> Inactive: <?= htmlspecialchars($employeeStats['admins']['inactive']) ?></div>
            <div class="announcement"><strong>Dentists</strong> - Active: <?= htmlspecialchars($employeeStats['dentists']['active']) ?> Inactive: <?= htmlspecialchars($employeeStats['dentists']['inactive']) ?></div>
            <hr>
            <div class="announcement"><strong>Total</strong> - Active: <?= htmlspecialchars($employeeStats['total']['active']) ?> Inactive: <?= htmlspecialchars($employeeStats['total']['inactive']) ?></div>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">notifications</span> Recent Notifications</h2>

            <?php if (count($notifications) === 0): ?>
                <div class="announcement">No notifications</div>
            <?php else: ?>
                <?php foreach (array_slice($notifications, 0, 3) as $n): ?>
                    <div class="announcement <?= $n['is_read'] ? '' : 'unread' ?>">
                        <div class="notif-message"><?= htmlspecialchars($n['message']) ?></div>
                        <div class="notif-date"><?= date('M d, Y H:i', strtotime($n['date_created'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2><span class="material-symbols-outlined">bolt</span> Quick Links</h2>
            <div class="quick-links">
                <a href="<?= BASE_URL ?>/Owner/pages/reports.php" ><span class="material-symbols-outlined">finance</span> Reports</a>
                <a href="<?= BASE_URL ?>/Owner/pages/employees.php"><span class="material-symbols-outlined">groups</span> Manage Employees</a>
                <a href="<?= BASE_URL ?>/Owner/pages/profile.php"><span class="material-symbols-outlined">manage_accounts</span> Profile Settings</a>
            </div>
        </div>

    </div>
</div>


<?php require_once BASE_PATH . '/includes/footer.php'; ?>
