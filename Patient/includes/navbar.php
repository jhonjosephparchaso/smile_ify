<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Smile-ify/includes/config.php';
require_once BASE_PATH . '/includes/db.php';
require_once BASE_PATH . '/processes/load_notifications.php';
require_once BASE_PATH . '/includes/auto_cancel_pending_reschedule.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/Patient/css/style.css?v=<?= time(); ?>" />

<nav>
    <div class="nav-container">
        <button class="menu-toggle">&#9776;</button>
        <ul class="nav-menu">
            <li>
                <a href="<?= BASE_URL ?>/Patient/index.php" class="<?= ($currentPage == 'index') ? 'active' : '' ?>">
                    <span class="link-text">Home</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/Patient/pages/calendar.php" class="<?= ($currentPage == 'calendar') ? 'active' : '' ?>">
                    <span class="link-text">Calendar</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/Patient/pages/profile.php" class="<?= ($currentPage == 'profile') ? 'active' : '' ?>">
                    <span class="link-text">Profile</span>
                </a>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link" id="notifDropdownToggle">
                    <span class="link-text">Notifications</span>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notif-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <div class="notif-dropdown" id="notifDropdown">
                    <h4>Notifications</h4>
                    <ul>
                        <?php if (count($notifications) === 0): ?>
                            <li class="notif-item">No notifications</li>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <?php
                                    $needsAction = ($n['appointment_status'] === 'Pending Reschedule');
                                    $handled = in_array($n['appointment_status'], ['Cancelled', 'Booked']);
                                ?>
                                <li 
                                    class="notif-item
                                        <?= $n['is_read'] ? '' : 'unread' ?>
                                        <?= $needsAction ? 'needs-action' : '' ?>
                                        <?= $handled ? 'handled' : '' ?>"
                                    data-id="<?= $n['notification_id'] ?>"
                                    data-appointment-id="<?= $n['appointment_transaction_id'] ?>"
                                >
                                    <span class="notif-message"><?= htmlspecialchars($n['message']) ?></span>
                                    <span class="notif-date"><?= date('M d, Y H:i', strtotime($n['date_created'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <a href="#" id="markAllRead">Mark all as read</a>
                </div>
            </li>
            <li>
                <a href="#" id="logoutLink">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="link-text">Logout</span>
                </a>

                <div id="logoutModal" class="logout-modal">
                    <div class="logout-modal-content">
                        <p>Are you sure you want to log out?</p>
                        <div class="modal-buttons">
                            <button id="confirmLogout">Yes, log out</button>
                            <button id="cancelLogout">Cancel</button>
                        </div>
                    </div>
                </div>
            </li>
            <li class="logged-user">
                Logged in as <span class="colon">:</span> 
                <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> 
                <span class="dash">-</span> 
                <span class="user-role">
                    <?= htmlspecialchars(ucfirst($_SESSION['role'] ?? '')) ?>
                </span>
            </li>
        </ul>
    </div>
</nav>

<div id="rescheduleModal" class="manage-calendar-modal" style="display:none;">
    <div class="manage-calendar-modal-content" style="max-width: 600px;">
        <div id="rescheduleModalBody">
            <p>Loading proposed schedule...</p>
        </div>
    </div>
</div>

<div id="cancelConfirmModal" class="manage-calendar-modal" style="display:none;">
    <div class="manage-calendar-modal-content">
        <p>
            Are you sure you want to cancel this appointment?
        </p>
        <div class="button-group">
            <button id="confirmCancelBtn" class="form-button confirm-btn">
                Yes, Cancel
            </button>
            <button id="cancelCancelBtn" class="form-button cancel-btn">
                No
            </button>
        </div>
    </div>
</div>

<div id="reschedConfirmModal" class="manage-calendar-modal" style="display:none;">
    <div class="manage-calendar-modal-content">
        <h3>Confirm Reschedule</h3>
        <p>Are you sure you want to confirm this new schedule?</p>

        <div class="button-group">
            <button id="confirmReschedBtn" class="form-button confirm-btn">
                Yes, Confirm
            </button>
            <button id="cancelReschedBtn" class="form-button cancel-btn">
                No
            </button>
        </div>
    </div>
</div>

<style>
.resched-section {
    margin-top: 20px;
}

.resched-section h3 {
    margin-bottom: 12px;
}

.resched-section .row {
    display: grid;
    grid-template-columns: 140px 1fr;
    column-gap: 12px;
    margin-bottom: 8px;
    align-items: center;
}

.resched-section .label {
    font-weight: 600;
    white-space: nowrap;
}

.notif-item.needs-action {
    border-left: 5px solid #e7973cff;
    background-color: #fff3f3;
    font-weight: 600;
    cursor: pointer;
}

.notif-item.handled {
    border-left: 5px solid #2ecc71;
    background-color: #f0fff6;
    opacity: 0.9;
}


</style>